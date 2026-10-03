<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Generic in-portal (database) notification used for every trainee alert:
 * enrolment approved, class completed, assignment graded, new announcement, fee recorded, etc.
 */
class PortalAlert extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $body = '',
        public ?string $url = null,
        public string $icon = 'Bell',
        public string $color = 'primary',
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body'  => $this->body,
            'url'   => $this->url,
            'icon'  => $this->icon,
            'color' => $this->color,
        ];
    }
}
