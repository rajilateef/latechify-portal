<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class TrainingProgram extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_active'          => 'boolean',
        'fee'                => 'integer',
        'siwes_fee'          => 'integer',
        'discount_fee'       => 'integer',
        'discount_siwes_fee' => 'integer',
    ];

    /* ── Fees ──
       `listFeeForType()` is the headline price (struck through when discounted);
       `feeForType()` is what's actually charged, so checkout and enrolments
       pick up a promotion automatically. */

    /** The undiscounted fee for a trainee type (IT/SIWES falls back to the full-time fee when unset). */
    public function listFeeForType(?string $type): int
    {
        return $type === 'it_siwes' ? (int) ($this->siwes_fee ?: $this->fee) : (int) $this->fee;
    }

    /** The promotional fee for a trainee type, or null when there isn't a valid one. */
    public function discountForType(?string $type): ?int
    {
        if ($type === 'it_siwes') {
            // IT/SIWES uses its own discount. When it has no fee of its own it is
            // billed at full-time rates, so it inherits the full-time discount too.
            $discount = $this->discount_siwes_fee ?: ($this->siwes_fee ? 0 : $this->discount_fee);
        } else {
            $discount = $this->discount_fee;
        }

        $list = $this->listFeeForType($type);

        // Ignore an absent discount, and any that doesn't actually save money.
        return $discount > 0 && $discount < $list ? (int) $discount : null;
    }

    public function hasDiscountForType(?string $type): bool
    {
        return $this->discountForType($type) !== null;
    }

    /** The fee actually charged — discount when present, otherwise the list fee. */
    public function feeForType(?string $type): int
    {
        return $this->discountForType($type) ?? $this->listFeeForType($type);
    }

    /** Whole-number percentage saved, e.g. 20 for "20% off". */
    public function discountPercentForType(?string $type): ?int
    {
        $list = $this->listFeeForType($type);
        $discount = $this->discountForType($type);

        return $list > 0 && $discount !== null
            ? (int) round(($list - $discount) / $list * 100)
            : null;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(TrainingCourse::class)->orderBy('sort_order');
    }

    /** All classes across every course in this program. */
    public function classes(): HasManyThrough
    {
        return $this->hasManyThrough(TrainingClass::class, TrainingCourse::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    public function liveSessions(): HasMany
    {
        return $this->hasMany(LiveSession::class);
    }

    /** All assignments across every course in this program. */
    public function assignments(): HasManyThrough
    {
        return $this->hasManyThrough(Assignment::class, TrainingCourse::class);
    }

    public function classesCount(): int
    {
        return $this->classes()->count();
    }

    public function classIds(): array
    {
        return $this->classes()->pluck('training_classes.id')->all();
    }

    /* ── Composite programs (a program that bundles other programs) ── */

    /** Programs this one includes (e.g. Fullstack → [Frontend, Backend]). */
    public function components(): BelongsToMany
    {
        return $this->belongsToMany(TrainingProgram::class, 'program_components', 'training_program_id', 'component_program_id')
            ->withPivot('sort_order')->orderByPivot('sort_order');
    }

    /** Composite programs that include this one. */
    public function partOf(): BelongsToMany
    {
        return $this->belongsToMany(TrainingProgram::class, 'program_components', 'component_program_id', 'training_program_id');
    }

    public function isComposite(): bool
    {
        return $this->relationLoaded('components') ? $this->components->isNotEmpty() : $this->components()->exists();
    }

    public function componentIds(): array
    {
        return $this->relationLoaded('components')
            ? $this->components->pluck('id')->all()
            : $this->components()->pluck('training_programs.id')->all();
    }

    /** This program plus every program it includes (one level). */
    public function effectiveProgramIds(): array
    {
        return array_values(array_unique([$this->id, ...$this->componentIds()]));
    }

    /** All courses this program grants access to (its own + included programs'), ordered. */
    public function effectiveCourses()
    {
        return TrainingCourse::whereIn('training_program_id', $this->effectiveProgramIds())
            ->orderBy('training_program_id')->orderBy('sort_order')->get();
    }

    public function effectiveCoursesCount(): int
    {
        return TrainingCourse::whereIn('training_program_id', $this->effectiveProgramIds())->count();
    }

    public function effectiveClassIds(): array
    {
        return TrainingClass::whereHas('course', fn ($q) => $q->whereIn('training_program_id', $this->effectiveProgramIds()))
            ->pluck('id')->all();
    }

    public function effectiveClassesCount(): int
    {
        return count($this->effectiveClassIds());
    }

    /** Ordered [self, ...components] as full programs — for grouped display on the portal. */
    public function programGroup()
    {
        return collect([$this])->concat($this->components()->get());
    }
}
