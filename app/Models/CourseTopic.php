<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseTopic extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];

    public function module()
    {
        return $this->belongsTo(CourseModule::class, 'course_module_id');
    }

    public function resources(): HasMany
    {
        return $this->hasMany(TopicResource::class)->orderBy('sort_order');
    }
}
