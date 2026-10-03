<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'due_at'       => 'datetime',
        'is_published' => 'boolean',
        'max_score'    => 'integer',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(TrainingCourse::class, 'training_course_id');
    }

    public function trainingClass(): BelongsTo
    {
        return $this->belongsTo(TrainingClass::class, 'training_class_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function submissionFor(?User $user): ?AssignmentSubmission
    {
        return $user ? $this->submissions()->where('user_id', $user->id)->first() : null;
    }

    public function isOverdue(): bool
    {
        return $this->due_at && $this->due_at->isPast();
    }
}
