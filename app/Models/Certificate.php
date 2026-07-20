<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'issue_date' => 'date',
    ];
}
