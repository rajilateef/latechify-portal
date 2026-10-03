<?php

namespace App\Http\Controllers;

use App\Models\Application;

/**
 * Legacy course-application payments. The apply/Paystack flow was retired on
 * 2026-10-01 in favour of Checkout — nothing creates new applications any more,
 * so this only serves bank-transfer details for records taken before the switch.
 */
class PaymentController extends Controller
{
    public function bankTransfer(Application $application)
    {
        return view('pages.bank-transfer', [
            'application' => $application,
        ]);
    }
}
