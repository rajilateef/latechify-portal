<?php

namespace App\Http\Controllers;

use App\Models\CheckoutOrder;
use App\Services\PaymentConfirmer;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        return view('pages.checkout', [
            // ?course= is the current form; ?program= is still honoured for older links.
            'selectedSlug' => $request->query('course') ?: $request->query('program'),
            'format'       => $request->query('format'),
        ]);
    }

    /**
     * Browser redirect back from Monnify. UX only — the webhook confirms the same
     * thing independently, and both re-verify server-side.
     */
    public function callback(Request $request, PaymentConfirmer $confirmer)
    {
        $order = CheckoutOrder::where('uuid', $request->query('order'))->first();

        if (! $order) {
            return view('pages.checkout-result', ['success' => false, 'order' => null]);
        }

        $success = $confirmer->confirmOrder($order);

        // A trainee topping up a balance belongs back in the portal, not on the marketing site.
        if ($order->kind === 'fee_topup') {
            return redirect()->route('portal.fees')->with(
                $success ? 'success' : 'notice',
                $success
                    ? 'Payment received — ₦'.number_format($order->amount).' has been applied to your balance.'
                    : "We couldn't confirm that payment yet. If you were debited it will appear here shortly.",
            );
        }

        return view('pages.checkout-result', ['success' => $success, 'order' => $order->fresh()]);
    }

    /** Bank-transfer instructions for an order awaiting manual payment. */
    public function transfer(CheckoutOrder $order)
    {
        return view('pages.checkout-transfer', ['order' => $order]);
    }

    /** Status page an applicant can return to at any time. */
    public function status(CheckoutOrder $order)
    {
        return view('pages.checkout-result', [
            'success' => $order->isPaid(),
            'order'   => $order,
        ]);
    }
}
