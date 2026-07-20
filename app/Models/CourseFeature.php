<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseFeature extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'included' => 'boolean',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
