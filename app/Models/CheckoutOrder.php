<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CheckoutOrder extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'amount'       => 'integer',
        'meta'         => 'array',
        'paid_at'      => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (CheckoutOrder $order) {
            $order->uuid ??= (string) Str::uuid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class, 'training_program_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    /* ── Scopes ── */

    /** Paid online/by transfer but still waiting on the super admin. */
    public function scopeAwaitingConfirmation($query)
    {
        return $query->where('status', 'paid');
    }

    /* ── State ── */

    public function isPaid(): bool
    {
        return in_array($this->status, ['paid', 'confirmed'], true);
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function programName(): string
    {
        return $this->program?->name ?: ($this->program_name ?: 'Training program');
    }

    /** What the customer actually bought — the course where there is one. */
    public function itemName(): string
    {
        return $this->course?->title ?: ($this->course_name ?: $this->programName());
    }

    public function formatLabel(): string
    {
        return $this->format === 'physical' ? 'On-campus' : 'Online';
    }

    public function typeLabel(): string
    {
        return $this->type === 'it_siwes' ? 'IT / SIWES' : 'Full-time';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending'   => 'Awaiting payment',
            'paid'      => 'Paid — awaiting confirmation',
            'confirmed' => 'Confirmed — portal active',
            'rejected'  => 'Rejected',
            default     => ucfirst((string) $this->status),
        };
    }

    /**
     * Mark a verified payment. Idempotent — a confirmed order is never rewound,
     * and the gateway payload is snapshotted for the audit trail.
     */
    public function markPaid(?array $gatewayPayload = null): void
    {
        if ($this->isConfirmed()) {
            return;
        }

        $this->update([
            'status'  => 'paid',
            'paid_at' => $this->paid_at ?? now(),
            'meta'    => $gatewayPayload ?? $this->meta,
        ]);
    }
}
