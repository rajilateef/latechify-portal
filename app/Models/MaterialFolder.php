<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialFolder extends Model
{
    protected $guarded = ['id'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MaterialFolder::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MaterialFolder::class, 'parent_id')->orderBy('sort_order');
    }

    public function files(): HasMany
    {
        return $this->hasMany(MaterialFile::class)->orderBy('title');
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    /** Breadcrumb-style path, e.g. "Frontend / HTML resources". */
    public function path(): string
    {
        $names = [];
        $node = $this;
        $guard = 0;
        while ($node && $guard++ < 10) {
            array_unshift($names, $node->name);
            $node = $node->parent;
        }

        return implode(' / ', $names);
    }
}
