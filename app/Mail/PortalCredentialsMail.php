<?php

namespace App\Mail;

use App\Models\CheckoutOrder;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PortalCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  string|null  $password  Temporary password — null when the trainee
     *                                 already had an account and keeps their own.
     */
    public function __construct(
        public CheckoutOrder $order,
        public User $student,
        public ?string $password = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your '.setting('site_name', 'Latechify').' portal is ready — '.$this->order->itemName(),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.portal-credentials',
            with: [
                'order'    => $this->order,
                'student'  => $this->student,
                'password' => $this->password,
                'loginUrl' => route('portal.login'),
            ],
        );
    }
}
