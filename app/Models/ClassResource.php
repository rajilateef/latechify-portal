<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ClassResource extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_published' => 'boolean',
        'file_size'    => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (ClassResource $resource) {
            // Capture size/name from the stored file so the portal can show them
            // without touching the disk on every render.
            if ($resource->type === 'file' && $resource->isDirty('file_path') && $resource->file_path) {
                $disk = Storage::disk($resource->disk ?: 'public');

                if ($disk->exists($resource->file_path)) {
                    $resource->file_name = $resource->file_name ?: basename($resource->file_path);
                    $resource->file_size = $disk->size($resource->file_path);
                }
            }
        });
    }

    public function trainingClass(): BelongsTo
    {
        return $this->belongsTo(TrainingClass::class, 'training_class_id');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function isFile(): bool
    {
        return $this->type === 'file' && (bool) $this->file_path;
    }

    public function isExternal(): bool
    {
        return ! $this->isFile();
    }

    /**
     * Where the trainee should be sent. Uploads always go through the portal's
     * permission check; links go straight out.
     */
    public function accessUrl(): ?string
    {
        if ($this->isFile()) {
            return route('portal.resources.open', $this);
        }

        return $this->url ?: ($this->file_path ? media_url($this->file_path) : null);
    }

    /**
     * Legacy helper kept for the admin side: the raw public URL of an upload.
     * Prefer accessUrl() anywhere a trainee is the audience.
     */
    public function link(): ?string
    {
        if ($this->file_path && ($this->disk ?: 'public') === 'public') {
            return media_url($this->file_path);
        }

        return $this->url ?: $this->accessUrl();
    }

    /**
     * A trainee may open this resource when it's published and they can reach the
     * program the owning class belongs to.
     */
    public function canBeAccessedBy(?User $user): bool
    {
        if (! $user || ! $this->is_published) {
            return false;
        }

        $programId = $this->trainingClass?->course?->training_program_id;

        return $programId && $user->canAccessProgram($programId);
    }

    public function extension(): string
    {
        return strtoupper(pathinfo($this->file_name ?: $this->file_path ?: '', PATHINFO_EXTENSION) ?: $this->type);
    }

    public function humanSize(): ?string
    {
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

    /** A lucide icon name for the resource's kind. */
    public function icon(): string
    {
        return match ($this->type) {
            'video' => 'Play',
            'link'  => 'ExternalLink',
            default => match (strtolower($this->extension())) {
                'pdf' => 'FileText',
                'doc', 'docx' => 'FileType',
                'ppt', 'pptx' => 'Presentation',
                'xls', 'xlsx', 'csv' => 'Sheet',
                'zip', 'rar' => 'FileArchive',
                'png', 'jpg', 'jpeg', 'gif', 'webp', 'svg' => 'Image',
                default => 'File',
            },
        };
    }

    /** Short human label for the action this resource offers. */
    public function actionLabel(): string
    {
        return match (true) {
            $this->isFile()        => 'Download',
            $this->type === 'video' => 'Watch video',
            default                 => 'Open link',
        };
    }
}
