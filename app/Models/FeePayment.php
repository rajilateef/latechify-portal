<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FeePayment extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'amount'  => 'integer',
        'paid_at' => 'datetime',
        'meta'    => 'array',   // verified gateway payload for online payments
    ];

    protected static function booted(): void
    {
        // Every recorded payment automatically gets a receipt.
        static::created(function (FeePayment $payment) {
            Receipt::issueFor($payment);
        });
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function receipt(): HasOne
    {
        return $this->hasOne(Receipt::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
