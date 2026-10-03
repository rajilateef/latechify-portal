<?php

namespace App\Services;

use App\Models\CheckoutOrder;
use App\Models\Enrollment;
use App\Models\FeePayment;
use App\Models\User;
use App\Notifications\PortalAlert;

/**
 * The single place a verified online payment turns into a fee payment on a
 * trainee's ledger. Creating the FeePayment also issues its receipt.
 */
class FeeLedger
{
    /**
     * Bank a paid checkout order against an enrolment. Idempotent — guarded on both
     * our reference and the gateway's transaction id, so a webhook racing the browser
     * callback (or a re-confirmation) can never double-credit.
     */
    public function recordFromOrder(
        CheckoutOrder $order,
        Enrollment $enrollment,
        User $student,
        ?User $admin = null,
    ): ?FeePayment {
        if ($order->amount <= 0 || $this->alreadyBanked($order)) {
            return null;
        }

        $payment = FeePayment::create([
            'enrollment_id'         => $enrollment->id,
            'user_id'               => $student->id,
            'amount'                => $order->amount,
            'method'                => $order->payment_method === 'monnify' ? 'monnify' : 'bank_transfer',
            'reference'             => $order->payment_reference,
            'transaction_reference' => $order->transaction_reference,
            'note'                  => ($order->kind === 'fee_topup' ? 'Portal payment — ' : 'Website checkout — ').$order->itemName(),
            'paid_at'               => $order->paid_at ?? now(),
            'recorded_by'           => $admin?->id,
            'meta'                  => $order->meta,
        ]);

        $this->notifyStudent($student, $enrollment->fresh(), $payment);

        return $payment;
    }

    /** Has this exact transaction already been credited? */
    protected function alreadyBanked(CheckoutOrder $order): bool
    {
        // With no reference at all there is nothing to match on — never treat that as
        // "already banked" (an unconditioned exists() would match any payment).
        if (! $order->payment_reference && ! $order->transaction_reference) {
            return false;
        }

        return FeePayment::query()
            ->where(function ($query) use ($order) {
                $query->when($order->payment_reference, fn ($q, $ref) => $q->orWhere('reference', $ref))
                    ->when($order->transaction_reference, fn ($q, $ref) => $q->orWhere('transaction_reference', $ref));
            })
            ->exists();
    }

    protected function notifyStudent(User $student, Enrollment $enrollment, FeePayment $payment): void
    {
        try {
            $student->notify(new PortalAlert(
                title: 'Payment received',
                body: '₦'.number_format($payment->amount).' recorded for '.$enrollment->program?->name
                    .'. Balance: ₦'.number_format($enrollment->outstanding()).'.',
                url: route('portal.fees'),
                icon: 'Wallet',
                color: 'success',
            ));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
