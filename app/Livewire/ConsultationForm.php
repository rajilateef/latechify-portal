<?php

namespace App\Livewire;

use App\Models\Consultation;
use App\Notifications\GenericAdminAlert;
use Illuminate\Support\Facades\Notification;
use Livewire\Component;

class ConsultationForm extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $service = '';
    public string $preferred_date = '';
    public string $preferred_time = '';
    public string $message = '';
    public bool $sent = false;

    public array $services = [
        'web-development' => 'Web Development', 'mobile-app' => 'Mobile App Development',
        'bootcamp' => 'Coding Bootcamp', 'consulting' => 'Digital Consulting', 'other' => 'Other',
    ];

    public array $times = [
        '09:00 AM', '10:00 AM', '11:00 AM', '12:00 PM', '01:00 PM',
        '02:00 PM', '03:00 PM', '04:00 PM', '05:00 PM',
    ];

    protected function rules(): array
    {
        return [
            'name'           => 'required|min:2',
            'email'          => 'required|email',
            'phone'          => 'required|min:7',
            'service'        => 'required',
            'preferred_date' => 'required|date|after_or_equal:today',
            'preferred_time' => 'required',
            'message'        => 'nullable|min:10|max:500',
        ];
    }

    public function submit(): void
    {
        $data = $this->validate();

        $consultation = Consultation::create($data + ['status' => 'pending']);

        try {
            if ($to = setting('notification_email')) {
                Notification::route('mail', $to)->notify(new GenericAdminAlert(
                    'New consultation request',
                    "{$consultation->name} ({$consultation->email}) requested a consultation on {$consultation->preferred_date->format('M j, Y')} at {$consultation->preferred_time}.",
                    url('/admin')
                ));
            }
        } catch (\Throwable $e) {
            report($e);
        }

        $this->reset(['name', 'email', 'phone', 'service', 'preferred_date', 'preferred_time', 'message']);
        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.consultation-form');
    }
}
