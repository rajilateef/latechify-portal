<?php

namespace App\Services;

use App\Models\CampRegistration;
use App\Models\CheckoutOrder;
use App\Notifications\GenericAdminAlert;
use Illuminate\Support\Facades\Notification;

/**
 * The single place a Monnify payment is confirmed, shared by the browser callback
 * and the webhook. Both paths re-verify against Monnify rather than trusting the
 * payload they were handed, and every confirmation is idempotent.
 */
class PaymentConfirmer
{
    public function __construct(
        protected Monnify $monnify,
        protected FeeLedger $ledger,
    ) {}

    /**
     * Verify a checkout order's own transaction and mark it paid.
     * Guards the amount and blocks a transaction being reused across orders.
     */
    public function confirmOrder(CheckoutOrder $order): bool
    {
        if ($order->isPaid()) {
            return true;
        }

        // Only ever verify the reference we generated and stored for THIS order.
        if (! $order->transaction_reference) {
            return false;
        }

        $data = $this->monnify->verify($order->transaction_reference);

        if (! $data) {
            return false;
        }

        $boundToOrder = ($data['paymentReference'] ?? null) === $order->payment_reference;
        $amountOk = ((int) ($data['amountPaid'] ?? 0)) >= (int) $order->amount;
        $notReused = ! CheckoutOrder::where('id', '!=', $order->id)
            ->where('transaction_reference', $order->transaction_reference)
            ->whereIn('status', ['paid', 'confirmed'])
            ->exists();

        if (! ($boundToOrder && $amountOk && $notReused)) {
            return false;
        }

        $order->markPaid($data);

        // A balance top-up lands on the ledger immediately — the portal already
        // exists, so there is nothing for an admin to confirm.
        if ($order->kind === 'fee_topup') {
            $this->bankTopup($order);
        } else {
            $this->alertAdmin($order);
        }

        return true;
    }

    /** Verify a camp registration's own transaction and mark it paid. */
    public function confirmCamp(CampRegistration $registration): bool
    {
        if ($registration->status === 'paid') {
            return true;
        }

        if (! $registration->transaction_reference) {
            return false;
        }

        $data = $this->monnify->verify($registration->transaction_reference);

        if (! $data) {
            return false;
        }

        $boundToRegistration = ($data['paymentReference'] ?? null) === $registration->payment_reference;
        $amountOk = ((int) ($data['amountPaid'] ?? 0)) >= (int) $registration->amount;
        $notReused = ! CampRegistration::where('id', '!=', $registration->id)
            ->where('transaction_reference', $registration->transaction_reference)
            ->where('status', 'paid')
            ->exists();

        if (! ($boundToRegistration && $amountOk && $notReused)) {
            return false;
        }

        $registration->update([
            'status'  => 'paid',
            'paid_at' => now(),
            'meta'    => $data,
        ]);

        return true;
    }

    /** Credit a verified balance payment to the trainee's enrolment immediately. */
    protected function bankTopup(CheckoutOrder $order): void
    {
        $enrollment = $order->enrollment;
        $student = $order->user;

        if (! $enrollment || ! $student) {
            return;
        }

        $this->ledger->recordFromOrder($order, $enrollment, $student);

        $order->update(['status' => 'confirmed', 'confirmed_at' => now()]);
    }

    /** Tell the super admin there's a paid order waiting to be confirmed. */
    protected function alertAdmin(CheckoutOrder $order): void
    {
        try {
            if ($to = setting('notification_email')) {
                Notification::route('mail', $to)->notify(new GenericAdminAlert(
                    'Payment received — portal awaiting confirmation',
                    "{$order->full_name} paid ₦".number_format($order->amount)." for {$order->itemName()} ({$order->formatLabel()})"
                        ." (ref {$order->payment_reference}). Confirm it in the admin to create their portal.",
                    url('/admin/checkout-orders'),
                ));
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
