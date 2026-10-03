<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * An audit record of one webhook delivery from Monnify. Written for every call —
 * including rejected and unmatched ones — so payment confirmation is diagnosable.
 */
class MonnifyWebhookEvent extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'payload'         => 'array',
        'signature_valid' => 'boolean',
        'amount_paid'     => 'integer',
        'received_at'     => 'datetime',
    ];

    public function scopeFailed($query)
    {
        return $query->whereIn('status', ['failed', 'unmatched']);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'handled'   => 'Handled',
            'ignored'   => 'Ignored (not a payment event)',
            'unmatched' => 'No matching record',
            'failed'    => 'Verification failed',
            default     => ucfirst((string) $this->status),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'handled'             => 'success',
            'ignored'             => 'gray',
            'unmatched', 'failed' => 'warning',
            default               => 'gray',
        };
    }
}
