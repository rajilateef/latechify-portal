<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TopicResource extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];

    public function topic()
    {
        return $this->belongsTo(CourseTopic::class, 'course_topic_id');
    }
}
