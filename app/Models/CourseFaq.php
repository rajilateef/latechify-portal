<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseFaq extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
