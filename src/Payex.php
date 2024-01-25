<?php

namespace Laraditz\Payex;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Laraditz\Payex\Models\PayexPayment;
use Laraditz\Payex\Models\PayexToken;
use LogicException;
use Illuminate\Support\Str;

class Payex
{
    private string $email;
    private string $key;
    private string $secret;
    private bool $sandboxMode = false;
    private string $currencyCode;
    private string $baseUrl;

    public function __construct()
    {
        $this->setEmail(config('payex.email'));
        $this->setKey(config('payex.key'));
        $this->setSecret(config('payex.secret'));
        $this->setSandboxMode(config('payex.sandbox.mode'));
        $this->setCurrencyCode(config('payex.currency_code'));
        $this->setBaseUrl();
    }

    public function authToken(): array
    {
        throw_if(!$this->getEmail(), LogicException::class, 'Email is not set.');
        throw_if(!$this->getSecret(), LogicException::class, 'Secret is Key not set.');

        $payexToken = PayexToken::where('email', $this->getEmail())->first();

        if ($payexToken && $payexToken->expires_at?->isFuture()) {
            return [
                'token' => $payexToken->token,
                'expiration' => $payexToken->expires_at?->toDateTimeString(),
            ];
        }

        $response = Http::withBasicAuth($this->getEmail(), $this->getSecret())
            ->acceptJson()
            ->post($this->getUrl('auth_token'));

        $response->throw();

        $result = $response->json();

        if (data_get($result, 'token')) {
            PayexToken::updateOrCreate([
                'email' => $this->getEmail()
            ], [
                'token' => data_get($result, 'token'),
                'expires_at' => data_get($result, 'expiration'),
            ]);
        }

        return $result;
    }

    public function createPayment(array $requestPayload = [])
    {
        $payload = $this->getPaymentPayload($requestPayload);

        $token = $this->authToken();

        throw_if(!$token, LogicException::class, 'Failed to get token.');

        $storeData = $this->prepareStoreData($payload);


        $payment = PayexPayment::create($storeData);

        throw_if(!$payment, LogicException::class, 'Cant create request in database table.');

        $payload = $this->preparePayload($payload);

        try {
            $response = $this->makeRequest(
                method: 'post',
                endPoint: $this->getUrl('payment_intent'),
                payload: [$payload]
            );

            $resp = $response->json();

            $result = data_get($resp, 'result.0');

            if (data_get($resp, 'status') == '00' && $result) {
                $payment->update([
                    'status' => data_get($resp, 'status'),
                    'status_description' => data_get($resp, 'message') ?? null,
                    'response' => $result
                ]);

                return [
                    'status' => true,
                    'id' => $payment->id,
                    'ref_no' => $payment->ref_no,
                    'currency_code' => $this->getCurrencyCode(),
                    'key' => data_get($result, 'key'),
                    'payment_url' => data_get($result, 'url'),
                ];
            } else {
                $payment->update([
                    'status' => data_get($resp, 'status'),
                    'status_description' => data_get($resp, 'message') ?? null,
                ]);

                return [
                    'status' => false,
                    'message' => data_get($resp, 'message') ?? 'Failed to create payment.',
                ];
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getTransaction(string $id)
    {
        $endPoint = Str::replace('{id}', $id, $this->getUrl('transactions.one'));

        try {
            $response = $this->makeRequest(
                method: 'get',
                endPoint: $endPoint
            );

            $resp = $response->json();

            return $resp;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getTransactions(array $params = [])
    {
        try {
            $response = $this->makeRequest(
                method: 'get',
                endPoint: $this->getUrl('transactions.index'),
                payload: $params
            );

            $resp = $response->json();

            throw_if(data_get($resp, 'status') != '00', LogicException::class, 'Failed to get transactions.');

            return [
                'result' => data_get($resp, 'result'),
                'total_pages' => data_get($resp, 'total_pages'),
            ];
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    private function makeRequest(string $method, string $endPoint, array|null $payload = null)
    {
        $token = $this->authToken();

        throw_if(!$token, LogicException::class, 'Failed to get token.');

        $response =  Http::withToken(data_get($token, 'token'))
            ->acceptJson()
            ->$method($endPoint, $payload);

        $response->throw();

        return $response;
    }

    private function preparePayload(array $payload): array
    {
        return collect($payload)->map(function ($value, $key) {
            return $value;
        })
            ->replace(['return_url' => route('payex.done')])
            ->toArray();
    }

    private function prepareStoreData(array $payload): array
    {
        $data = [
            'ref_no' => data_get($payload, 'reference_number'),
            'currency_code' => data_get($payload, 'currency'),
            'amount' => data_get($payload, 'amount'),
            'customer_name' => data_get($payload, 'customer_name'),
            'email' => data_get($payload, 'email'),
            'contact_no' => data_get($payload, 'contact_number'),
            'description' => data_get($payload, 'description'),
            'return_url' => data_get($payload, 'return_url'),
            'callback_url' => data_get($payload, 'callback_url'),
        ];

        $metadata = Arr::except($payload, [
            'reference_number', 'currency', 'amount', 'customer_name', 'email',
            'contact_number', 'description', 'return_url', 'callback_url'
        ]);

        $data['metadata'] = count($metadata) > 0 ? $metadata : null;

        return $data;
    }

    private function getPaymentPayload(array $requestPayload)
    {
        throw_if(!data_get($requestPayload, 'amount'), LogicException::class, 'Missing amount.');
        throw_if(!data_get($requestPayload, 'customer_name'), LogicException::class, 'Missing customer_name.');
        throw_if(!data_get($requestPayload, 'return_url'), LogicException::class, 'Missing return_url.');

        return [
            'reference_number' => data_get($requestPayload, 'reference_number') ?? $this->generateRefNo(),
            'currency' => $this->getCurrencyCode(),
            'callback_url' => $this->getCallbackUrl(),
        ] + $requestPayload;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setSandboxMode(bool $sandboxMode): void
    {
        $this->sandboxMode = $sandboxMode;
    }

    public function getSandboxMode(): bool
    {
        return $this->sandboxMode;
    }

    public function setBaseUrl(): void
    {
        if ($this->getSandboxMode() === true) {
            $this->baseUrl = config('payex.sandbox.base_url');
        } else {
            $this->baseUrl = config('payex.base_url');
        }
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getUrl($route): string
    {
        $route = config('payex.routes.' . $route);
        return $this->getBaseUrl() .  $route;
    }

    public function setCurrencyCode($currencyCode): void
    {
        $this->currencyCode = $currencyCode;
    }

    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    public function setKey($key): void
    {
        $this->key = $key;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setSecret($secret): void
    {
        $this->secret = $secret;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function getCallbackUrl(): string
    {
        return config('payex.callback_url') ?? route('payex.callback');
    }

    private function generateRefNo()
    {
        $ref_no = $this->randomAlphanumeric();

        while (PayexPayment::where('ref_no', $ref_no)->count()) {
            $ref_no = $this->randomAlphanumeric();
        }

        return $ref_no;
    }

    private function randomAlphanumeric(int $length = 8)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(str_shuffle($characters), 0, $length);
    }
}
