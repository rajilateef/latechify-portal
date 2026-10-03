<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LiveSession extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'starts_at'    => 'datetime',
        'ends_at'      => 'datetime',
        'is_cancelled' => 'boolean',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class, 'training_program_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(TrainingCourse::class, 'training_course_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('is_cancelled', false)->where('starts_at', '>=', now())->orderBy('starts_at');
    }

    public function scopePast($query)
    {
        return $query->where('starts_at', '<', now())->orderByDesc('starts_at');
    }

    public function isLive(): bool
    {
        return ! $this->is_cancelled
            && $this->starts_at->isPast()
            && ($this->ends_at ? $this->ends_at->isFuture() : $this->starts_at->diffInHours(now()) < 3);
    }

    public function attendanceFor(?User $user): ?Attendance
    {
        return $user ? $this->attendances()->where('user_id', $user->id)->first() : null;
    }
}
