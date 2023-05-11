<?php

namespace Laraditz\Payex\Http\Controllers;

use Illuminate\Http\Request;
use Laraditz\Payex\Events\CallbackReceived;
use Laraditz\Payex\Models\PayexMessage;
use Laraditz\Payex\Models\PayexPayment;

class PayexController extends Controller
{
    public function done(Request $request)
    {
        if (config('payex.log_request') === true) {
            logger()->info('Payex done: Received', $request->all());
        }

        $payment = PayexPayment::where('ref_no', $request->reference_number)->firstOrFail();
        $data = $request->all();
        $status_code = data_get($data, 'auth_code');
        $status_text = $this->getStatusText($data);
        $description = $this->getDescription($data);

        $this->updatePayment('done', $data);

        return view('payex::payex.done', compact('payment', 'data', 'status_code', 'status_text', 'description'));
    }

    public function callback(Request $request)
    {
        if (config('payex.log_request') === true) {
            logger()->info('Payex callback: Received', $request->all());
        }

        $this->updatePayment('callback', $request->all());
    }

    private function updatePayment(string $action, array $data)
    {
        $ref_no = data_get($data, 'reference_number');
        $status = data_get($data, 'auth_code');
        $description = data_get($data, 'response');

        if ($ref_no && $status) { // only proceed if ref no and status exists

            event(new CallbackReceived($data));

            PayexMessage::create([
                'action' => $action,
                'response' => $data,
            ]);

            $payment = PayexPayment::where('ref_no', $ref_no)->first();

            // update only if status not yet success or new status is also success. only update status from callback.
            if ($payment && $action === 'callback' && ($payment->payment_status != '00' || $status == '00')) {
                $payment->payment_status = $status;
                $payment->payment_description = $description;
                $payment->callback_response = $data;

                $payment->save();
            }
        } else {
            logger()->info('Payex callback : Missing some parameters.', $data);
            exit;
        }
    }

    private function getStatusText(array $data)
    {
        $code  = data_get($data, 'auth_code');

        if ($code == '00') {
            return 'success';
        } elseif ($code == '09' || $code == '99') {
            return 'pending';
        } else {
            return 'failed';
        }
    }

    private function getDescription(array $data)
    {
        return data_get($data, 'response') ?? null;
    }
}
