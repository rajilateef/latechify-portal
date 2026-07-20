<?php

namespace App\Livewire;

use App\Models\Application;
use App\Models\Course;
use App\Notifications\GenericAdminAlert;
use App\Services\Paystack;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ApplyForm extends Component
{
    public string $full_name = '';
    public string $email = '';
    public string $phone = '';
    public string $course = '';
    public string $education = '';
    public string $experience = '';
    public string $class_format = 'online';
    public string $motivation = '';
    public string $heard_about = '';
    public string $payment_method = 'paystack';

    public array $educationOptions = [
        'high-school' => 'High School', 'associate' => 'Associate Degree', 'bachelor' => "Bachelor's Degree",
        'master' => "Master's Degree", 'doctorate' => 'Doctorate', 'other' => 'Other',
    ];

    public array $experienceOptions = [
        'none' => 'None', 'beginner' => 'Beginner (less than 1 year)',
        'intermediate' => 'Intermediate (1-3 years)', 'advanced' => 'Advanced (3+ years)',
    ];

    public array $heardOptions = [
        'search' => 'Search Engine', 'social' => 'Social Media', 'friend' => 'Friend/Colleague',
        'event' => 'Event/Conference', 'other' => 'Other',
    ];

    public function mount(?string $selectedSlug = null, string $classFormat = 'online'): void
    {
        $this->course = $selectedSlug && Course::where('slug', $selectedSlug)->exists()
            ? $selectedSlug
            : (Course::active()->value('slug') ?? '');
        $this->class_format = in_array($classFormat, ['online', 'physical']) ? $classFormat : 'online';
    }

    #[Computed]
    public function courses()
    {
        return Course::active()->get();
    }

    #[Computed]
    public function selectedCourse(): ?Course
    {
        return Course::where('slug', $this->course)->first();
    }

    #[Computed]
    public function price(): int
    {
        return $this->selectedCourse()?->priceFor($this->class_format) ?? 0;
    }

    protected function rules(): array
    {
        return [
            'full_name'      => 'required|min:2',
            'email'          => 'required|email',
            'phone'          => 'required|min:7',
            'course'         => 'required|exists:courses,slug',
            'education'      => 'required',
            'experience'     => 'required',
            'class_format'   => 'required|in:online,physical',
            'motivation'     => 'required|min:20',
            'heard_about'    => 'required',
            'payment_method' => 'required|in:paystack,transfer',
        ];
    }

    public function submit(Paystack $paystack)
    {
        $this->validate();

        $course = $this->selectedCourse();
        $price = $this->price();

        $application = Application::create([
            'full_name'      => $this->full_name,
            'email'          => $this->email,
            'phone'          => $this->phone,
            'course_id'      => $course?->id,
            'package'        => $course ? $course->title.' Course' : null,
            'price'          => $price,
            'class_format'   => $this->class_format,
            'payment_method' => $this->payment_method,
            'education'      => $this->education,
            'experience'     => $this->experience,
            'motivation'     => $this->motivation,
            'heard_about'    => $this->heard_about,
            'status'         => 'pending',
        ]);

        try {
            if ($to = setting('notification_email')) {
                Notification::route('mail', $to)->notify(new GenericAdminAlert(
                    'New course application',
                    "{$application->full_name} applied for {$application->package} ({$application->class_format}, ₦".number_format($price).").",
                    url('/admin')
                ));
            }
        } catch (\Throwable $e) {
            report($e);
        }

        if ($this->payment_method === 'paystack' && $price > 0) {
            $payment = $application->payments()->create([
                'method'   => 'paystack',
                'amount'   => $price,
                'status'   => 'pending',
                'currency' => 'NGN',
            ]);

            $result = $paystack->initialize(
                $this->email,
                $price,
                route('payment.callback'),
                ['application_id' => $application->id, 'course' => $this->course, 'class_format' => $this->class_format],
            );

            if ($result) {
                $payment->update(['reference' => $result['reference']]);

                return $this->redirect($result['authorization_url']);
            }

            session()->flash('notice', 'Online payment is temporarily unavailable. Please complete your enrollment via bank transfer below.');
        }

        return $this->redirect(route('payment.bank-transfer', $application));
    }

    public function render()
    {
        return view('livewire.apply-form');
    }
}
