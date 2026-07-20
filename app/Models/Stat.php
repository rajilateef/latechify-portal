<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stat extends Model
{
    protected $guarded = ['id'];

    public function scopeGroup($query, string $group)
    {
        return $query->where('group', $group)->orderBy('sort_order');
    }
}
