<?php

namespace App\Livewire;

use App\Models\CheckoutOrder;
use App\Models\Course;
use App\Notifications\GenericAdminAlert;
use App\Services\Monnify;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CheckoutForm extends Component
{
    public string $full_name = '';
    public string $email = '';
    public string $phone = '';
    public string $course = '';          // course slug
    public string $format = 'online';    // online | physical
    public string $note = '';
    public string $payment_method = 'monnify';

    public array $formatOptions = [
        'online'   => 'Online',
        'physical' => 'On-campus',
    ];

    /**
     * Param names must NOT match the public properties — Livewire assigns incoming
     * params to typed properties before mount() runs, so a null would fatal.
     *
     * @param  string|null  $selectedSlug    a course slug, or a program slug from a legacy /checkout?program= link
     * @param  string|null  $selectedFormat  'online' or 'physical'
     */
    public function mount(?string $selectedSlug = null, ?string $selectedFormat = null): void
    {
        $this->course = $this->resolveSlug($selectedSlug);

        if (in_array($selectedFormat, ['online', 'physical'], true)) {
            $this->format = $selectedFormat;
        }

        // Don't preselect a gateway that isn't configured — offer transfer instead.
        if (! $this->onlineAvailable()) {
            $this->payment_method = 'transfer';
        }
    }

    /** Accept a course slug, fall back to matching a program slug, else the first course. */
    protected function resolveSlug(?string $slug): string
    {
        $sellable = $this->courses();

        if ($slug) {
            if ($sellable->firstWhere('slug', $slug)) {
                return $slug;
            }

            // Legacy ?program= deep links still land on the right course.
            $byProgram = $sellable->first(fn (Course $c) => $c->trainingProgram?->slug === $slug);

            if ($byProgram) {
                return $byProgram->slug;
            }
        }

        return (string) ($sellable->first()?->slug ?? '');
    }

    /**
     * Courses that can actually be bought: active, and mapped to an active training
     * program (the portal enrolment needs one).
     */
    #[Computed]
    public function courses()
    {
        return Course::active()
            ->with('trainingProgram')
            ->get()
            ->filter(fn (Course $c) => $c->trainingProgram?->is_active)
            ->values();
    }

    #[Computed]
    public function selectedCourse(): ?Course
    {
        return $this->course ? $this->courses()->firstWhere('slug', $this->course) : null;
    }

    /** What the customer pays — the course's promotional price when there is one. */
    #[Computed]
    public function amount(): int
    {
        return (int) ($this->selectedCourse()?->payablePriceFor($this->format) ?? 0);
    }

    /** The undiscounted price, for the struck-through figure. */
    #[Computed]
    public function listAmount(): ?int
    {
        $course = $this->selectedCourse();

        return $course?->hasDiscountFor($this->format) ? $course->priceFor($this->format) : null;
    }

    #[Computed]
    public function onlineAvailable(): bool
    {
        return app(Monnify::class)->isConfigured();
    }

    protected function rules(): array
    {
        return [
            'full_name'      => 'required|min:2|max:120',
            'email'          => 'required|email|max:190',
            'phone'          => 'required|min:7|max:30',
            'course'         => 'required|exists:courses,slug',
            'format'         => 'required|in:online,physical',
            'note'           => 'nullable|max:500',
            'payment_method' => 'required|in:monnify,transfer',
        ];
    }

    public function submit(Monnify $monnify)
    {
        $this->validate();

        $course = $this->selectedCourse();

        if (! $course) {
            $this->addError('course', 'That course is not available for enrolment right now.');

            return null;
        }

        $program = $course->trainingProgram;
        $amount = $this->amount();

        $order = CheckoutOrder::create([
            'full_name'           => $this->full_name,
            'email'               => $this->email,
            'phone'               => $this->phone,
            'course_id'           => $course->id,
            'course_name'         => $course->title,
            'training_program_id' => $program?->id,
            'program_name'        => $program?->name,
            'format'              => $this->format,
            'type'                => 'full_time',
            'amount'              => $amount,
            'payment_method'      => $this->payment_method,
            'note'                => $this->note ?: null,
            'status'              => 'pending',
        ]);

        $this->alertAdmin($order);

        if ($this->payment_method === 'monnify' && $amount > 0 && $monnify->isConfigured()) {
            // Every reference we hand Monnify is LATECHIFY-prefixed.
            $paymentReference = Monnify::reference('CHK', $order->id);

            $result = $monnify->initialize(
                $order->full_name,
                $order->email,
                $amount,
                $paymentReference,
                route('checkout.callback', ['order' => $order->uuid]),
                'Tuition — '.$order->itemName(),
            );

            if ($result) {
                $order->update([
                    'payment_reference'     => $paymentReference,
                    'transaction_reference' => $result['transaction_reference'],
                ]);

                return $this->redirect($result['checkout_url']);
            }

            session()->flash('notice', 'We could not open the card payment page just now, so we have reserved your place for bank transfer instead. Your details are saved — pay with the reference below, or try card payment again later.');
        }

        // Transfer (or gateway unavailable): give them our bank details and a reference to quote.
        $order->update([
            'payment_method'    => 'transfer',
            'payment_reference' => $order->payment_reference ?: Monnify::reference('CHK', $order->id),
        ]);

        return $this->redirect(route('checkout.transfer', ['order' => $order->uuid]));
    }

    protected function alertAdmin(CheckoutOrder $order): void
    {
        try {
            if ($to = setting('notification_email')) {
                Notification::route('mail', $to)->notify(new GenericAdminAlert(
                    'New checkout started',
                    "{$order->full_name} is enrolling for {$order->itemName()} ({$order->formatLabel()}, ₦"
                        .number_format($order->amount).').',
                    url('/admin'),
                ));
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function render()
    {
        return view('livewire.checkout-form');
    }
}
