<?php

namespace App\Livewire;

use App\Models\ContactMessage;
use App\Notifications\GenericAdminAlert;
use Illuminate\Support\Facades\Notification;
use Livewire\Component;

class ContactForm extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $subject = '';
    public string $message = '';
    public bool $sent = false;

    protected function rules(): array
    {
        return [
            'name'    => 'required|min:2',
            'email'   => 'required|email',
            'phone'   => 'nullable|string',
            'subject' => 'nullable|string',
            'message' => 'required|min:10',
        ];
    }

    public array $subjects = [
        'general'     => 'General Inquiry',
        'course'      => 'Course Information',
        'service'     => 'Custom Development Services',
        'support'     => 'Technical Support',
        'partnership' => 'Partnership Opportunities',
        'other'       => 'Other',
    ];

    public function submit(): void
    {
        $data = $this->validate();

        $message = ContactMessage::create($data + ['status' => 'new']);

        try {
            if ($to = setting('notification_email')) {
                Notification::route('mail', $to)->notify(new GenericAdminAlert(
                    'New contact message',
                    "{$message->name} ({$message->email}) sent a message:\n\n{$message->message}",
                    route('contact')
                ));
            }
        } catch (\Throwable $e) {
            report($e);
        }

        $this->reset(['name', 'email', 'phone', 'subject', 'message']);
        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
