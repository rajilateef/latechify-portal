<?php

namespace App\Models;

use App\Notifications\PortalAlert;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'notified_at'  => 'datetime',
    ];

    protected static function booted(): void
    {
        // When an announcement becomes published, notify its audience once.
        static::saved(function (Announcement $announcement) {
            if ($announcement->is_published && ! $announcement->notified_at) {
                $announcement->notifyAudience();
            }
        });
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class, 'training_program_id');
    }

    /** Send an in-portal alert to every active trainee who can see this announcement. */
    public function notifyAudience(): void
    {
        $query = User::where('is_student', true)->where('is_active', true);

        if ($this->training_program_id) {
            $query->whereHas('activeEnrollments', fn ($q) => $q->where('training_program_id', $this->training_program_id));
        }

        $query->get()->each(fn (User $user) => $user->notify(new PortalAlert(
            title: 'New announcement: '.$this->title,
            body: \Illuminate\Support\Str::limit(strip_tags($this->body), 90),
            url: route('portal.announcements'),
            icon: 'Megaphone',
            color: 'primary',
        )));

        $this->forceFill(['notified_at' => now()])->saveQuietly();
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)->orderByDesc('published_at');
    }

    /** Announcements visible to a student: global ones + those for any program they can access (incl. composite components). */
    public function scopeVisibleTo($query, User $user)
    {
        $programIds = $user->accessibleProgramIds();

        return $query->where(function ($q) use ($programIds) {
            $q->whereNull('training_program_id')->orWhereIn('training_program_id', $programIds);
        });
    }
}
