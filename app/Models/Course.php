<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'discount_price_online'   => 'integer',
        'discount_price_physical' => 'integer',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** The portal training program this marketing course enrols into (drives Checkout). */
    public function trainingProgram(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class, 'training_program_id');
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

    /* ── Pricing ──
       `priceFor()` is the LIST price (struck through when discounted);
       `payablePriceFor()` is what the customer actually pays. */

    public function priceFor(string $format): int
    {
        return (int) ($format === 'online' ? $this->price_online : $this->price_physical);
    }

    /** The promotional price for a format, or null when there isn't a valid one. */
    public function discountFor(string $format): ?int
    {
        $discount = $format === 'online' ? $this->discount_price_online : $this->discount_price_physical;

        // Ignore a zero/absent discount, and any that doesn't actually save money.
        return $discount && $discount > 0 && $discount < $this->priceFor($format)
            ? (int) $discount
            : null;
    }

    public function hasDiscountFor(string $format): bool
    {
        return $this->discountFor($format) !== null;
    }

    /** What the customer pays — the discount when there is one, else the list price. */
    public function payablePriceFor(string $format): int
    {
        return $this->discountFor($format) ?? $this->priceFor($format);
    }

    /** Whole-number percentage saved, e.g. 20 for "20% off". */
    public function discountPercentFor(string $format): ?int
    {
        $list = $this->priceFor($format);
        $discount = $this->discountFor($format);

        return $list > 0 && $discount !== null
            ? (int) round(($list - $discount) / $list * 100)
            : null;
    }

    /** Where "Enrol" on this course should go, optionally preselecting a class format. */
    public function checkoutUrl(?string $format = null): string
    {
        $params = ['course' => $this->slug];

        if (in_array($format, ['online', 'physical'], true)) {
            $params['format'] = $format;
        }

        return route('checkout', $params);
    }
}
