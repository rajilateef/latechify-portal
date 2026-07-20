<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'tags'     => 'array',
        'popular'  => 'boolean',
        'featured' => 'boolean',
        'is_active'=> 'boolean',
        'rating'   => 'float',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function highlights(): HasMany
    {
        return $this->hasMany(CourseHighlight::class)->orderBy('sort_order');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(CourseModule::class)->orderBy('sort_order');
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(CourseFaq::class)->orderBy('sort_order');
    }

    public function features(): HasMany
    {
        return $this->hasMany(CourseFeature::class)->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function priceFor(string $format): int
    {
        return $format === 'online' ? $this->price_online : $this->price_physical;
    }
}
