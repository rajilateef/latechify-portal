<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseModule extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'is_detailed' => 'boolean',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function topics(): HasMany
    {
        return $this->hasMany(CourseTopic::class)->orderBy('sort_order');
    }
}
