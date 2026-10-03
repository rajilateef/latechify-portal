<?php

namespace App\Http\Controllers;

use App\Models\CampRegistration;
use App\Services\PaymentConfirmer;
use Illuminate\Http\Request;

class CampController extends Controller
{
    public function index()
    {
        return view('pages.camp');
    }

    /**
     * Browser redirect back from Monnify. This is UX only — the authoritative
     * confirmation also runs here and (independently) in the webhook.
     */
    public function paymentCallback(Request $request, PaymentConfirmer $confirmer)
    {
        $registration = CampRegistration::where('uuid', $request->query('reg'))->first();

        if (! $registration) {
            return view('pages.camp-result', ['success' => false, 'registration' => null]);
        }

        // Idempotent — the webhook may already have confirmed this.
        $success = $confirmer->confirmCamp($registration);

        return view('pages.camp-result', ['success' => $success, 'registration' => $registration->fresh()]);
    }

    public function manual(CampRegistration $registration)
    {
        return view('pages.camp-manual-payment', ['registration' => $registration]);
    }
}
