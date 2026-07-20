<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Advert extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_active'     => 'boolean',
        'show_floating' => 'boolean',
        'starts_at'     => 'datetime',
        'ends_at'       => 'datetime',
    ];

    /** Currently live adverts (active + within schedule window). */
    public function scopeLive(Builder $query): Builder
    {
        $now = now();

        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now))
            ->orderBy('sort_order');
    }

    public function scopeForSection(Builder $query): Builder
    {
        return $query->whereIn('placement', ['section', 'both']);
    }

    public function scopeForPopup(Builder $query): Builder
    {
        return $query->whereIn('placement', ['popup', 'both']);
    }

    /** Adverts flagged to appear as a floating card near the navbar. */
    public function scopeForFloating(Builder $query): Builder
    {
        return $query->where('show_floating', true);
    }
}
