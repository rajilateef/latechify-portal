<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class CourseMaterial extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_published' => 'boolean',
        'file_size'    => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (CourseMaterial $material) {
            // When attached to a library file, mirror its kind onto `type` for display/filtering.
            if ($material->material_file_id) {
                $file = $material->relationLoaded('materialFile')
                    ? $material->materialFile
                    : MaterialFile::find($material->material_file_id);

                if ($file) {
                    $material->type = in_array($file->kind, ['file', 'image']) ? 'file' : $file->kind;
                    $material->title = $material->title ?: $file->title;
                }
            }

            // Legacy path: capture metadata from a directly-uploaded file (no library link).
            if (! $material->material_file_id && $material->type === 'file' && $material->isDirty('file_path') && $material->file_path) {
                $disk = Storage::disk($material->disk ?: 'local');
                if ($disk->exists($material->file_path)) {
                    $material->file_name ??= basename($material->file_path);
                    $material->file_size = $disk->size($material->file_path);
                    try {
                        $material->mime = $disk->mimeType($material->file_path) ?: $material->mime;
                    } catch (\Throwable $e) {
                        // mimeType can throw for some drivers — non-fatal.
                    }
                }
            }
        });
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(TrainingCourse::class, 'training_course_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /** The reusable library file this material is attached to (when it's a file, not a link). */
    public function materialFile(): BelongsTo
    {
        return $this->belongsTo(MaterialFile::class);
    }

    /** Trainees explicitly granted access when access = 'selected'. */
    public function allowedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Permission check. A trainee must be actively enrolled in this material's program,
     * and — when access is restricted — be on the allow-list.
     */
    public function canBeAccessedBy(?User $user): bool
    {
        if (! $user || ! $this->is_published) {
            return false;
        }

        $programId = $this->course?->training_program_id;

        // Direct enrolment OR access via a composite (bundle) program that includes this program.
        if (! $programId || ! $user->canAccessProgram($programId)) {
            return false;
        }

        return $this->access === 'enrolled'
            || $this->allowedUsers()->whereKey($user->id)->exists();
    }

    /* ── File resolution (prefers the linked library file, falls back to own columns) ── */

    public function effectiveDisk(): string
    {
        return $this->materialFile?->disk ?: ($this->disk ?: 'local');
    }

    public function effectivePath(): ?string
    {
        return $this->materialFile?->file_path ?: $this->file_path;
    }

    public function effectiveFileName(): ?string
    {
        return $this->materialFile?->file_name ?: ($this->file_name ?: basename((string) $this->effectivePath()));
    }

    public function isFile(): bool
    {
        if ($this->materialFile) {
            return $this->materialFile->isFileKind();
        }

        return in_array($this->type, ['file', 'image']) && (bool) $this->file_path;
    }

    /** External link, or a streamed download for uploaded files. */
    public function accessUrl(): ?string
    {
        if ($this->isFile()) {
            return route('portal.materials.download', $this);
        }

        return $this->url ?: $this->materialFile?->url;
    }

    public function humanSize(): ?string
    {
        if ($this->materialFile) {
            return $this->materialFile->humanSize();
        }

        if (! $this->file_size) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $i = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return round($size, $size >= 10 || $i === 0 ? 0 : 1).' '.$units[$i];
    }

    public function extension(): string
    {
        if ($this->materialFile) {
            return $this->materialFile->extension();
        }

        return strtoupper(pathinfo($this->file_name ?: $this->file_path ?: '', PATHINFO_EXTENSION) ?: $this->type);
    }

    /** A lucide icon name for the material's kind. */
    public function icon(): string
    {
        if ($this->materialFile) {
            return $this->materialFile->icon();
        }

        return match ($this->type) {
            'video' => 'Video',
            'image' => 'Image',
            'link'  => 'ExternalLink',
            default => match (strtolower($this->extension())) {
                'pdf' => 'FileText',
                'doc', 'docx' => 'FileType',
                'ppt', 'pptx' => 'Presentation',
                'xls', 'xlsx', 'csv' => 'Sheet',
                'zip', 'rar' => 'FileArchive',
                default => 'File',
            },
        };
    }
}
