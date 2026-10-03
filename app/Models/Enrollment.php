<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Enrollment extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'started_at'  => 'datetime',
        'ends_at'     => 'datetime',
        'approved_at' => 'datetime',
        'fee_amount'  => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class, 'training_program_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(FeePayment::class)->latest('paid_at');
    }

    public function certificate(): HasOne
    {
        return $this->hasOne(Certificate::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /* ── Fees ── */

    public function feeAmount(): int
    {
        // An explicit per-enrolment amount always wins (e.g. a family/relative discount);
        // otherwise fall back to the program's default fee for this trainee's type.
        if ($this->fee_amount !== null) {
            return (int) $this->fee_amount;
        }

        return (int) ($this->program?->feeForType($this->type) ?? 0);
    }

    public function typeLabel(): string
    {
        return $this->type === 'it_siwes' ? 'IT / SIWES' : 'Full-time';
    }

    public function amountPaid(): int
    {
        return (int) $this->payments()->sum('amount');
    }

    public function outstanding(): int
    {
        return max(0, $this->feeAmount() - $this->amountPaid());
    }

    public function isFullyPaid(): bool
    {
        return $this->feeAmount() > 0 && $this->outstanding() === 0;
    }

    public function paymentPercent(): int
    {
        $fee = $this->feeAmount();

        return $fee ? (int) round(min($this->amountPaid(), $fee) / $fee * 100) : 0;
    }

    /* ── Progress + time helpers ── */

    public function totalClasses(): int
    {
        return $this->program?->effectiveClassesCount() ?? 0;
    }

    public function completedClassesCount(): int
    {
        $ids = $this->program?->effectiveClassIds() ?? [];

        return empty($ids) ? 0 : ClassCompletion::query()
            ->where('user_id', $this->user_id)
            ->whereIn('training_class_id', $ids)
            ->count();
    }

    public function progressPercent(): int
    {
        $total = $this->totalClasses();

        return $total ? (int) round($this->completedClassesCount() / $total * 100) : 0;
    }

    public function totalDays(): ?int
    {
        return ($this->started_at && $this->ends_at)
            ? max(1, (int) $this->started_at->diffInDays($this->ends_at))
            : null;
    }

    public function daysSpent(): ?int
    {
        if (! $this->started_at) {
            return null;
        }

        $spent = (int) $this->started_at->diffInDays(now());
        $total = $this->totalDays();

        return $total ? min($spent, $total) : $spent;
    }

    public function daysRemaining(): ?int
    {
        return $this->ends_at ? max(0, (int) now()->diffInDays($this->ends_at, false)) : null;
    }

    public function timeProgressPercent(): int
    {
        $total = $this->totalDays();

        return $total ? (int) round(min($this->daysSpent() ?? 0, $total) / $total * 100) : 0;
    }

    /** Fully completed = every class in the program marked done. */
    public function isComplete(): bool
    {
        return $this->totalClasses() > 0 && $this->completedClassesCount() >= $this->totalClasses();
    }

    /** The next class the student has not completed yet, or null when finished. */
    public function nextClass(): ?TrainingClass
    {
        $programIds = $this->program?->effectiveProgramIds() ?? [$this->training_program_id];

        $completed = ClassCompletion::where('user_id', $this->user_id)
            ->whereIn('training_class_id', $this->program?->effectiveClassIds() ?? [])
            ->pluck('training_class_id')->all();

        return TrainingClass::query()
            ->join('training_courses', 'training_courses.id', '=', 'training_classes.training_course_id')
            ->whereIn('training_courses.training_program_id', $programIds)
            ->whereNotIn('training_classes.id', $completed)
            ->orderBy('training_courses.training_program_id')
            ->orderBy('training_courses.sort_order')
            ->orderBy('training_classes.sort_order')
            ->select('training_classes.*')
            ->first();
    }
}
