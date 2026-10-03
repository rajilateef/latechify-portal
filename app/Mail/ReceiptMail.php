<?php

namespace App\Mail;

use App\Models\Receipt;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Receipt $receipt) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment receipt '.$this->receipt->receipt_number.' — '.setting('site_name', 'Latechify'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.receipt',
            with: ['receipt' => $this->receipt],
        );
    }

    public function attachments(): array
    {
        $pdf = Pdf::loadView('receipts.document', ['receipt' => $this->receipt, 'preview' => false])->setPaper('a4');

        return [
            \Illuminate\Mail\Mailables\Attachment::fromData(fn () => $pdf->output(), 'Receipt-'.$this->receipt->receipt_number.'.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
