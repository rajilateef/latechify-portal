<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GenericAdminAlert extends Notification
{
    public function __construct(
        public string $subject,
        public string $body,
        public ?string $actionUrl = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->subject)
            ->greeting($this->subject);

        foreach (explode("\n", $this->body) as $line) {
            if (trim($line) !== '') {
                $mail->line($line);
            }
        }

        if ($this->actionUrl) {
            $mail->action('View in dashboard', url('/admin'));
        }

        return $mail;
    }
}
