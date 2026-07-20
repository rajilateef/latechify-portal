<?php

namespace App\Http\Controllers;

use App\Models\CampRegistration;
use App\Services\Monnify;
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
    public function paymentCallback(Request $request, Monnify $monnify)
    {
        $registration = CampRegistration::where('uuid', $request->query('reg'))->first();

        if (! $registration) {
            return view('pages.camp-result', ['success' => false, 'registration' => null]);
        }

        // Idempotent: already confirmed (e.g. webhook beat the redirect).
        if ($registration->status === 'paid') {
            return view('pages.camp-result', ['success' => true, 'registration' => $registration]);
        }

        if ($this->confirm($registration, $monnify)) {
            return view('pages.camp-result', ['success' => true, 'registration' => $registration]);
        }

        return view('pages.camp-result', ['success' => false, 'registration' => $registration]);
    }

    /**
     * Server-to-server Monnify webhook — the reliable source of truth for
     * confirmation (fires even if the payer never returns to the browser).
     */
    public function webhook(Request $request, Monnify $monnify)
    {
        // Authenticate: Monnify signs the raw body with SHA-512 HMAC of the secret key.
        $signature = $request->header('monnify-signature');
        $computed = hash_hmac('sha512', $request->getContent(), (string) $monnify->secretKey());

        if (! $signature || ! hash_equals($computed, $signature)) {
            abort(401);
        }

        $data = $request->input('eventData', []);
        $reference = $data['paymentReference'] ?? null;

        if ($reference) {
            $registration = CampRegistration::where('payment_reference', $reference)->first();

            if ($registration && $registration->status !== 'paid') {
                // Re-verify server-side rather than trusting the webhook body.
                $this->confirm($registration, $monnify);
            }
        }

        return response()->json(['status' => 'ok']);
    }

    public function manual(CampRegistration $registration)
    {
        return view('pages.camp-manual-payment', ['registration' => $registration]);
    }

    /**
     * Verify a registration's OWN Monnify transaction and mark it paid.
     * Binds the transaction to the registration and guards amount + reuse.
     */
    protected function confirm(CampRegistration $registration, Monnify $monnify): bool
    {
        if ($registration->status === 'paid') {
            return true;
        }

        // Only ever verify the reference we generated & stored for THIS registration.
        if (! $registration->transaction_reference) {
            return false;
        }

        $data = $monnify->verify($registration->transaction_reference);

        if (! $data) {
            return false;
        }

        $boundToRegistration = ($data['paymentReference'] ?? null) === $registration->payment_reference;
        $amountOk = ((int) ($data['amountPaid'] ?? 0)) >= (int) $registration->amount;
        $notReused = ! CampRegistration::where('id', '!=', $registration->id)
            ->where('transaction_reference', $registration->transaction_reference)
            ->where('status', 'paid')
            ->exists();

        if ($boundToRegistration && $amountOk && $notReused) {
            $registration->update([
                'status'  => 'paid',
                'paid_at' => now(),
                'meta'    => $data,
            ]);

            return true;
        }

        return false;
    }
}
