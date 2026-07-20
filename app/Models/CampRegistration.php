<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CampRegistration extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'paid_at' => 'datetime',
        'meta'    => 'array',
    ];

    protected static function booted(): void
    {
        // Unguessable public identifier used for the manual-payment page (avoids IDOR).
        static::creating(function (CampRegistration $registration) {
            $registration->uuid ??= (string) Str::uuid();
        });
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }
}
