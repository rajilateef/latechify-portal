<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingCourse extends Model
{
    protected $guarded = ['id'];

    public function program(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class, 'training_program_id');
    }

    public function classes(): HasMany
    {
        return $this->hasMany(TrainingClass::class)->orderBy('sort_order');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class)->orderBy('sort_order');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(CourseMaterial::class)->orderBy('sort_order');
    }
}
