<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingClass extends Model
{
    protected $guarded = ['id'];

    public function course(): BelongsTo
    {
        return $this->belongsTo(TrainingCourse::class, 'training_course_id');
    }

    public function resources(): HasMany
    {
        return $this->hasMany(ClassResource::class)->orderBy('sort_order');
    }

    public function completions(): HasMany
    {
        return $this->hasMany(ClassCompletion::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function program(): ?TrainingProgram
    {
        return $this->course?->program;
    }
}
