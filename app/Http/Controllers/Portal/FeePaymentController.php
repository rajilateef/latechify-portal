<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\CheckoutOrder;
use App\Models\Enrollment;
use App\Services\Monnify;
use Illuminate\Http\Request;

class FeePaymentController extends Controller
{
    /**
     * Start a Monnify payment for part (or all) of an enrolment's outstanding balance.
     * The transaction is verified on the shared checkout callback/webhook.
     */
    public function pay(Request $request, Enrollment $enrollment, Monnify $monnify)
    {
        $user = auth()->user();

        abort_unless($enrollment->user_id === $user->id, 403);

        $outstanding = $enrollment->outstanding();

        if ($outstanding <= 0) {
            return back()->with('notice', 'This program is already fully paid.');
        }

        // Validate before anything else, so an out-of-range amount is always rejected
        // — never silently swallowed by a gateway outage.
        $data = $request->validate([
            'amount' => 'required|integer|min:100|max:'.$outstanding,
        ], [
            'amount.max' => 'You can pay at most ₦'.number_format($outstanding).' on this program.',
        ]);

        if (! $monnify->isConfigured()) {
            return back()->with('notice', 'Online payment is not available right now — please contact the administrator.');
        }

        $order = CheckoutOrder::create([
            'kind'                => 'fee_topup',
            'full_name'           => $user->displayName(),
            'email'               => $user->email,
            'phone'               => $user->phone ?: '-',
            'training_program_id' => $enrollment->training_program_id,
            'program_name'        => $enrollment->program?->name,
            'type'                => $enrollment->type,
            'amount'              => (int) $data['amount'],
            'payment_method'      => 'monnify',
            'status'              => 'pending',
            'user_id'             => $user->id,
            'enrollment_id'       => $enrollment->id,
        ]);

        // Every reference we hand Monnify is LATECHIFY-prefixed.
        $paymentReference = Monnify::reference('FEE', $order->id);

        $result = $monnify->initialize(
            $order->full_name,
            $order->email,
            $order->amount,
            $paymentReference,
            route('checkout.callback', ['order' => $order->uuid]),
            'Training fee — '.$order->programName(),
        );

        if (! $result) {
            $order->delete();

            return back()->with('notice', 'We could not reach the payment gateway. Please try again shortly.');
        }

        $order->update([
            'payment_reference'     => $paymentReference,
            'transaction_reference' => $result['transaction_reference'],
        ]);

        return redirect()->away($result['checkout_url']);
    }
}
