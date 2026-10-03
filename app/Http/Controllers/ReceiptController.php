<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use Barryvdh\DomPDF\Facade\Pdf;

class ReceiptController extends Controller
{
    /** Print-friendly HTML preview (with a Download/Print bar). */
    public function show(Receipt $receipt)
    {
        $this->authorizeAccess($receipt);

        return view('receipts.document', [
            'receipt' => $receipt->load('issuedBy'),
            'preview' => true,
        ]);
    }

    /** Streamed PDF download. */
    public function download(Receipt $receipt)
    {
        $this->authorizeAccess($receipt);

        $pdf = Pdf::loadView('receipts.document', [
            'receipt' => $receipt->load('issuedBy'),
            'preview' => false,
        ])->setPaper('a4');

        return $pdf->download('Receipt-'.$receipt->receipt_number.'.pdf');
    }

    /** Only the paying student or a super admin may view a receipt. */
    protected function authorizeAccess(Receipt $receipt): void
    {
        $user = auth()->user();

        abort_unless($user && ($user->is_admin || $receipt->user_id === $user->id), 403);
    }
}
