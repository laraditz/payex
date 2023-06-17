# Laravel Payex

[![Latest Version on Packagist](https://img.shields.io/packagist/v/laraditz/payex.svg?style=flat-square)](https://packagist.org/packages/laraditz/payex)
[![Total Downloads](https://img.shields.io/packagist/dt/laraditz/payex.svg?style=flat-square)](https://packagist.org/packages/laraditz/payex)
![GitHub Actions](https://github.com/laraditz/payex/actions/workflows/main.yml/badge.svg)

A simple laravel package for Payex Payment Gateway.

## Installation

You can install the package via composer:

```bash
composer require laraditz/payex
```

## Before Start

Configure your variables in your `.env` (recommended) or you can publish the config file and change it there.

```
PAYEX_EMAIL=<your_payex_email_here>
PAYEX_KEY=<your_payex_key_here>
PAYEX_SECRET=<your_payex_secret_here>
PAYEX_SANDBOX_MODE=true # true or false for sandbox mode
```

(Optional) You can publish the config file via this command:
```bash
php artisan vendor:publish --provider="Laraditz\Payex\PayexServiceProvider" --tag="config"
```

Run the migration command to create the necessary database table.
```bash
php artisan migrate
```

## Available Methods
Below are all methods available under this package.

- `createPayment(array $requestPayload)` - Create payment intent.
- `getTransactions(array $params)` - Get all transactions.
- `getTransaction(string $id)` - Get specific transaction by `txn_id`.

## Usage

### Create Payment
To create payment and get the payment URL to be redirected to. You can use service container or facade.

```php
// Create a payment

// Using service container
$payex = app('payex')->createPayment([
            'amount' => '100', //in cents
            'customer_name' => 'Farhan',
            'description' => 'some description here',
            'return_url' => 'https://yourreturn.url'
        ]);

// Using facade
$payment = \Payex::createPayment([
            'amount' => '100', //in cents
            'customer_name' => 'Farhan',
            'description' => 'some description here',
            'return_url' => 'https://yourreturn.url'
        ]);
```

Return example:
```php
[
    "status" => true,
    "id" => "991f24s0-5470-41c5-9b3c-9841d72d32e5",
    "ref_no" => "Xvs9k43y",
    "currency_code" => "MYR",
    "key" => "f5e3168fef3b5ed7826c689a37dce58e",
    "payment_url" => "https://api.payex.io/Payment/Form/f5e3168fef3b5ed7826c689a37dce58e"
]
```

Redirect to the `payment_url` to proceed to Payex payment page. Once done, you will be redirected to the `return_url`. Below is the sample response returned.
```json
{
  "amount": "1",
  "currency": "MYR",
  "customer_name": "Farhan",
  "description": "some description here",
  "reference_number": "Xvs9k43y",
  "mandate_reference_number": null,
  "payment_intent": "f5e3168fef3b5ed7826c689a37dce58e",
  "collection_id": "zg3RcR5y",
  "invoice_id": null,
  "txn_id": "PX1068201c1315547307",
  "external_txn_id": "20230502211212840110171535215420691",
  "response": "SUCCESS",
  "auth_code": "00",
  "auth_number": null,
  "txn_date": "20230502075957",
  "fpx_mode": null,
  "fpx_buyer_name": null,
  "fpx_buyer_bank_id": null,
  "fpx_buyer_bank_name": null,
  "card_holder_name": null,
  "card_number": null,
  "card_expiry": null,
  "card_brand": "N.A.",
  "card_issuer": null,
  "card_on_file": null,
  "signature": "bcd39079a409751ebb4c64c1f8acc53cd0439896a731a513b6753e9f909d6a08a79a04cd5a9cf8d6d27d93206dfa35074ee607e790e242ee547407fa5af9f05a",
  "txn_type": "Touch 'n Go eWallet",
  "nonce": "eMygaAdk3cryXM5DSLoidNIQrwhW7b0wCmQ6CG0B6z5VNeBbm8yXhLjAPQcXL0WL",
  "metadata": "{}"
}
```

## Event

This package also provide some events to allow your application to listen to it. You can create your listener and register it under event below.

| Event                                     |  Description  
|-------------------------------------------|-----------------------|
| Laraditz\Payex\Events\CallbackReceived    | Received backend response from Payex for a payment. Can use to update your payment status and other details

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email raditzfarhan@gmail.com instead of using the issue tracker.

## Credits

-   [Raditz Farhan](https://github.com/laraditz)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
