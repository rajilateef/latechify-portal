<?php

namespace App\Http\Controllers;

use App\Models\CampRegistration;
use App\Models\CheckoutOrder;
use App\Models\MonnifyWebhookEvent;
use App\Services\Monnify;
use App\Services\PaymentConfirmer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * The single Monnify webhook endpoint.
 *
 * Monnify allows ONE transaction-completion URL per account, so this one route
 * serves every payment the site takes — course checkout, portal fee top-ups and
 * summer-camp registrations — dispatching on our LATECHIFY-<SCOPE>- reference.
 *
 * Register it in the Monnify dashboard (Settings → API Keys & Webhooks) as:
 *     https://<your-domain>/webhooks/monnify
 *
 * The delivery is NOT gated on the monnify-signature header — the header is only
 * recorded for the audit log. Security does not depend on it: this handler never
 * trusts the posted body. It looks the reference up in our own records and then
 * re-verifies the transaction directly with Monnify's API, so a forged call can
 * never mark anything paid. The route is rate limited to blunt log flooding.
 */
class MonnifyWebhookController extends Controller
{
    /** Monnify event types that mean "money arrived". */
    protected const PAYMENT_EVENTS = ['SUCCESSFUL_TRANSACTION', 'SUCCESSFUL_PAYMENT'];

    public function __invoke(Request $request, Monnify $monnify, PaymentConfirmer $confirmer): JsonResponse
    {
        $payload = $request->json()->all() ?: $request->all();
        $data = data_get($payload, 'eventData', []);

        $event = MonnifyWebhookEvent::create([
            'event_type'            => data_get($payload, 'eventType'),
            'payment_reference'     => data_get($data, 'paymentReference'),
            'transaction_reference' => data_get($data, 'transactionReference'),
            'payment_status'        => data_get($data, 'paymentStatus'),
            'amount_paid'           => (int) data_get($data, 'amountPaid', 0) ?: null,
            'payload'               => $payload,
            'received_at'           => now(),
            'status'                => 'received',
        ]);

        // Record whether the signature matched, but do not gate on it — every
        // payment is re-verified with Monnify before anything is marked paid.
        $event->update(['signature_valid' => $this->signatureIsValid($request, $monnify)]);

        // ── Only act on payment completion; acknowledge everything else ──
        $eventType = (string) $event->event_type;

        if ($eventType && ! in_array($eventType, self::PAYMENT_EVENTS, true)) {
            return $this->finish($event, 'ignored', "Not a payment event ({$eventType}).");
        }

        $reference = $event->payment_reference;

        if (! $reference) {
            return $this->finish($event, 'unmatched', 'Payload carried no paymentReference.');
        }

        // ── Dispatch on our own reference, falling back to a lookup in both tables ──
        if ($order = CheckoutOrder::where('payment_reference', $reference)->first()) {
            return $this->handleOrder($event, $order, $confirmer);
        }

        if ($registration = CampRegistration::where('payment_reference', $reference)->first()) {
            return $this->handleCamp($event, $registration, $confirmer);
        }

        return $this->finish($event, 'unmatched', "No checkout order or camp registration matches reference {$reference}.");
    }

    protected function handleOrder(MonnifyWebhookEvent $event, CheckoutOrder $order, PaymentConfirmer $confirmer): JsonResponse
    {
        $event->update(['handler' => 'checkout_order', 'handled_id' => $order->id]);

        if ($order->isPaid()) {
            // The browser callback (or an earlier delivery) already banked it.
            return $this->finish($event, 'handled', 'Already confirmed — nothing to do.');
        }

        // Re-verify against Monnify rather than trusting the webhook body.
        if ($confirmer->confirmOrder($order)) {
            return $this->finish($event, 'handled', "Order #{$order->id} confirmed as paid.");
        }

        return $this->finish($event, 'failed', "Order #{$order->id} could not be verified with Monnify.");
    }

    protected function handleCamp(MonnifyWebhookEvent $event, CampRegistration $registration, PaymentConfirmer $confirmer): JsonResponse
    {
        $event->update(['handler' => 'camp_registration', 'handled_id' => $registration->id]);

        if ($registration->status === 'paid') {
            return $this->finish($event, 'handled', 'Already confirmed — nothing to do.');
        }

        if ($confirmer->confirmCamp($registration)) {
            return $this->finish($event, 'handled', "Camp registration #{$registration->id} confirmed as paid.");
        }

        return $this->finish($event, 'failed', "Camp registration #{$registration->id} could not be verified with Monnify.");
    }

    /**
     * Informational only — the result is logged, never used to reject a delivery.
     * Monnify signs the raw body with an SHA-512 HMAC of the secret key.
     */
    protected function signatureIsValid(Request $request, Monnify $monnify): bool
    {
        $signature = $request->header('monnify-signature');
        $secret = (string) $monnify->secretKey();

        if (! $signature || $secret === '') {
            return false;
        }

        return hash_equals(hash_hmac('sha512', $request->getContent(), $secret), $signature);
    }

    /** Record the outcome and acknowledge. Monnify retries anything that isn't 2xx. */
    protected function finish(MonnifyWebhookEvent $event, string $status, string $message): JsonResponse
    {
        $event->update(['status' => $status, 'message' => $message]);

        Log::log(
            in_array($status, ['handled', 'ignored'], true) ? 'info' : 'warning',
            "[monnify-webhook] {$status}: {$message}",
            ['event_id' => $event->id, 'reference' => $event->payment_reference],
        );

        return response()->json(['status' => 'ok']);
    }
}
