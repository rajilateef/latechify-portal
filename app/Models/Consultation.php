<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'preferred_date' => 'date',
    ];
}
