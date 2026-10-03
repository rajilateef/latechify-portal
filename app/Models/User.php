<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'phone',
        'avatar_url',
        'bio',
        'is_admin',
        'is_student',
        'is_active',
        'last_login_at',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_student' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return (bool) $this->is_admin;
    }

    /* ── Training portal ── */

    public function enrollments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function activeEnrollments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->enrollments()->where('status', 'active');
    }

    public function classCompletions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ClassCompletion::class);
    }

    /** IDs of classes this student has completed. */
    public function completedClassIds(): array
    {
        return $this->classCompletions()->pluck('training_class_id')->all();
    }

    public function hasCompletedClass(int $classId): bool
    {
        return $this->classCompletions()->where('training_class_id', $classId)->exists();
    }

    public function submissions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function attendances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function feePayments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(FeePayment::class);
    }

    public function certificates(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Certificate::class)->latest('issue_date');
    }

    /* ── Portal-wide aggregates ── */

    /** Program ids the student is directly, actively enrolled in. */
    public function activeProgramIds(): array
    {
        return $this->activeEnrollments()->pluck('training_program_id')->all();
    }

    /**
     * All program ids the student can access content for — their active enrolments PLUS,
     * for any composite (bundle) program they're enrolled in, its included programs.
     */
    public function accessibleProgramIds(): array
    {
        $ids = [];

        foreach ($this->activeEnrollments()->with('program.components')->get() as $enrollment) {
            if ($enrollment->program) {
                $ids = array_merge($ids, $enrollment->program->effectiveProgramIds());
            }
        }

        return array_values(array_unique($ids));
    }

    public function canAccessProgram(int $programId): bool
    {
        return in_array($programId, $this->accessibleProgramIds());
    }

    /** Attendance rate (%) across all sessions the student was marked for. */
    public function attendanceRate(): ?int
    {
        $total = $this->attendances()->count();

        if ($total === 0) {
            return null;
        }

        $present = $this->attendances()->whereIn('status', ['present', 'late'])->count();

        return (int) round($present / $total * 100);
    }

    /** Total outstanding fees across every active enrolment. */
    public function totalOutstanding(): int
    {
        return $this->activeEnrollments()->with('program')->get()->sum->outstanding();
    }

    public function unreadNotificationsCount(): int
    {
        return $this->unreadNotifications()->count();
    }

    public function initials(): string
    {
        $name = $this->displayName();
        $parts = preg_split('/\s+/', trim($name));

        return strtoupper(mb_substr($parts[0] ?? '', 0, 1).(count($parts) > 1 ? mb_substr(end($parts), 0, 1) : ''));
    }

    public function displayName(): string
    {
        return $this->name ?: trim(($this->first_name ?? '').' '.($this->last_name ?? '')) ?: $this->email;
    }
}
