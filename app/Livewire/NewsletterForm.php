<?php

namespace App\Livewire;

use App\Models\NewsletterSubscriber;
use App\Notifications\GenericAdminAlert;
use Illuminate\Support\Facades\Notification;
use Livewire\Component;

class NewsletterForm extends Component
{
    public string $email = '';
    public bool $sent = false;

    public function submit(): void
    {
        $this->validate(['email' => 'required|email']);

        $subscriber = NewsletterSubscriber::firstOrCreate(['email' => $this->email]);

        if ($subscriber->wasRecentlyCreated) {
            try {
                if ($to = setting('notification_email')) {
                    Notification::route('mail', $to)->notify(new GenericAdminAlert(
                        'New newsletter subscriber',
                        "{$subscriber->email} subscribed to the newsletter.",
                        route('home'),
                    ));
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $this->reset('email');
        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.newsletter-form');
    }
}
