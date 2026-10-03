<?php

namespace App\Services;

use App\Mail\PortalCredentialsMail;
use App\Models\CheckoutOrder;
use App\Models\Enrollment;
use App\Models\User;
use App\Notifications\PortalAlert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Turns a confirmed checkout into a working portal: trainee account, active
 * enrolment, and the fee payment (which issues its own receipt) — all in one
 * transaction so a half-provisioned trainee can never exist.
 */
class PortalProvisioner
{
    public function __construct(protected FeeLedger $ledger) {}

    /**
     * Provision the portal for a paid order. Idempotent: an already-confirmed
     * order is returned untouched.
     *
     * @return array{order: CheckoutOrder, user: User, enrollment: Enrollment, password: ?string}
     */
    public function confirm(CheckoutOrder $order, ?User $admin = null): array
    {
        if ($order->isConfirmed() && $order->user && $order->enrollment) {
            return [
                'order'      => $order,
                'user'       => $order->user,
                'enrollment' => $order->enrollment,
                'password'   => null,
            ];
        }

        $password = null;

        $result = DB::transaction(function () use ($order, $admin, &$password) {
            [$user, $password] = $this->resolveUser($order);

            $enrollment = $this->resolveEnrollment($order, $user);

            // Bank the money the trainee already paid against the enrolment so the
            // fee ledger (and their receipt) reflects reality from day one.
            $this->ledger->recordFromOrder($order, $enrollment, $user, $admin);

            $order->update([
                'status'        => 'confirmed',
                'user_id'       => $user->id,
                'enrollment_id' => $enrollment->id,
                'confirmed_at'  => now(),
                'confirmed_by'  => $admin?->id,
            ]);

            return ['user' => $user, 'enrollment' => $enrollment];
        });

        $this->notify($order->fresh(), $result['user'], $password);

        return [
            'order'      => $order->fresh(),
            'user'       => $result['user'],
            'enrollment' => $result['enrollment'],
            'password'   => $password,
        ];
    }

    /** Reuse an existing account for this email, otherwise mint one with a temp password. */
    protected function resolveUser(CheckoutOrder $order): array
    {
        $existing = User::where('email', $order->email)->first();

        if ($existing) {
            // An admin buying a program stays an admin; just make sure they can sign in as a trainee.
            $existing->update(['is_student' => true, 'is_active' => true]);

            return [$existing, null];
        }

        $password = Str::password(10, symbols: false);

        $user = User::create([
            'name'       => $order->full_name,
            'email'      => $order->email,
            'phone'      => $order->phone,
            'password'   => Hash::make($password),
            'is_student' => true,
            'is_active'  => true,
        ]);

        return [$user, $password];
    }

    /** Activate (or create) the enrolment for the purchased program. */
    protected function resolveEnrollment(CheckoutOrder $order, User $user): Enrollment
    {
        $program = $order->program;

        $enrollment = Enrollment::firstOrNew([
            'user_id'             => $user->id,
            'training_program_id' => $order->training_program_id,
        ]);

        $enrollment->fill([
            'type'        => $order->type,
            'status'      => 'active',
            // Bill what they were actually quoted at checkout (course price for the
            // chosen format, promotion included) rather than the program's list fee.
            'fee_amount'  => $enrollment->fee_amount ?? ($order->amount ?: $program?->feeForType($order->type)),
            'fee_note'    => $enrollment->fee_note ?: trim($order->itemName().' — '.$order->formatLabel()),
            'started_at'  => $enrollment->started_at ?? now(),
            'ends_at'     => $enrollment->ends_at ?? now()->addWeeks($program?->duration_weeks ?? 12),
            'approved_at' => now(),
        ])->save();

        return $enrollment;
    }

    /** Email the credentials and drop an in-portal welcome notification. */
    protected function notify(CheckoutOrder $order, User $user, ?string $password): void
    {
        try {
            Mail::to($user->email)->send(new PortalCredentialsMail($order, $user, $password));
        } catch (\Throwable $e) {
            report($e);
        }

        try {
            $user->notify(new PortalAlert(
                title: 'Welcome to '.$order->itemName(),
                body: 'Your payment has been confirmed and your portal is ready. Start with your first class.',
                url: $order->training_program_id ? route('portal.program', $order->program) : route('portal.dashboard'),
                icon: 'PartyPopper',
                color: 'success',
            ));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
