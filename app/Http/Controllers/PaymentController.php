<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Payment;
use App\Services\Paystack;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function callback(Request $request, Paystack $paystack)
    {
        $reference = $request->query('reference') ?: $request->query('trxref');

        $payment = Payment::where('reference', $reference)->latest()->first();
        $application = $payment?->application;

        $data = $reference ? $paystack->verify($reference) : null;

        if ($data && $payment) {
            $payment->update([
                'status'      => 'success',
                'verified_at' => now(),
                'meta'        => $data,
            ]);
            $application?->update(['status' => 'paid', 'paid_at' => now(), 'reference' => $reference]);

            return view('pages.payment-result', [
                'success'     => true,
                'application' => $application,
            ]);
        }

        if ($payment) {
            $payment->update(['status' => 'failed']);
        }

        return view('pages.payment-result', [
            'success'     => false,
            'application' => $application,
        ]);
    }

    public function bankTransfer(Application $application)
    {
        return view('pages.bank-transfer', [
            'application' => $application,
        ]);
    }
}
