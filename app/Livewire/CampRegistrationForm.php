<?php

namespace App\Livewire;

use App\Models\CampRegistration;
use App\Notifications\GenericAdminAlert;
use App\Services\Monnify;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CampRegistrationForm extends Component
{
    public string $full_name = '';
    public string $email = '';
    public string $phone = '';
    public string $age_group = '';
    public string $track = '';
    public string $mode = 'physical';
    public string $experience = '';
    public string $note = '';
    public string $payment_method = 'monnify';

    public array $ageOptions = [
        '8-12'  => '8 – 12 years',
        '13-17' => '13 – 17 years',
        '18-24' => '18 – 24 years',
        '25+'   => '25 years & above',
    ];

    public array $experienceOptions = [
        'none'         => 'Complete beginner',
        'beginner'     => 'Some basics',
        'intermediate' => 'Intermediate',
    ];

    #[Computed]
    public function tracks(): array
    {
        $lines = collect(preg_split('/\r\n|\r|\n/', (string) setting('camp_tracks')))
            ->map(fn ($l) => trim($l))
            ->filter()
            ->values();

        return $lines->isNotEmpty()
            ? $lines->all()
            : ['Web Development', 'Mobile App Development', 'Game Development', 'Data & AI', 'Robotics'];
    }

    #[Computed]
    public function feePhysical(): int
    {
        return (int) setting('camp_fee_physical', 0);
    }

    #[Computed]
    public function feeVirtual(): int
    {
        return (int) setting('camp_fee_virtual', 0);
    }

    #[Computed]
    public function fee(): int
    {
        return $this->mode === 'virtual' ? $this->feeVirtual() : $this->feePhysical();
    }

    protected function rules(): array
    {
        return [
            'full_name'      => 'required|min:2',
            'email'          => 'required|email',
            'phone'          => 'required|min:7',
            'age_group'      => 'required',
            'track'          => 'required',
            'mode'           => 'required|in:physical,virtual',
            'experience'     => 'required',
            'note'           => 'nullable|max:500',
            'payment_method' => 'required|in:monnify,manual',
        ];
    }

    public function submit(Monnify $monnify)
    {
        $this->validate();

        $fee = $this->fee();

        $registration = CampRegistration::create([
            'full_name'      => $this->full_name,
            'email'          => $this->email,
            'phone'          => $this->phone,
            'age_group'      => $this->age_group,
            'track'          => $this->track,
            'mode'           => $this->mode,
            'experience'     => $this->experience,
            'note'           => $this->note ?: null,
            'amount'         => $fee,
            'payment_method' => $this->payment_method,
            'status'         => 'pending',
        ]);

        try {
            if ($to = setting('notification_email')) {
                Notification::route('mail', $to)->notify(new GenericAdminAlert(
                    'New Summer Camp registration',
                    "{$registration->full_name} registered for the Summer Coding Camp ({$registration->track}, ₦".number_format($fee).").",
                    url('/admin'),
                ));
            }
        } catch (\Throwable $e) {
            report($e);
        }

        if ($this->payment_method === 'monnify' && $fee > 0 && $monnify->isConfigured()) {
            $paymentReference = 'CAMP-'.$registration->id.'-'.Str::upper(Str::random(6));

            $result = $monnify->initialize(
                $registration->full_name,
                $registration->email,
                $fee,
                $paymentReference,
                route('camp.payment.callback', ['reg' => $registration->uuid]),
                'Summer Coding Camp registration',
            );

            if ($result) {
                $registration->update([
                    'payment_reference'     => $paymentReference,
                    'transaction_reference' => $result['transaction_reference'],
                ]);

                return $this->redirect($result['checkout_url']);
            }

            session()->flash('notice', 'Online payment is temporarily unavailable — please complete your registration via bank transfer below.');
        }

        return $this->redirect(route('camp.manual', ['registration' => $registration->uuid]));
    }

    public function render()
    {
        return view('livewire.camp-registration-form');
    }
}
