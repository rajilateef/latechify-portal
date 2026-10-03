<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class MaterialFile extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'file_size' => 'integer',
    ];

    protected static function booted(): void
    {
        // Capture file metadata from the stored upload when the path changes.
        static::saving(function (MaterialFile $file) {
            if (in_array($file->kind, ['file', 'image']) && $file->isDirty('file_path') && $file->file_path) {
                $disk = Storage::disk($file->disk ?: 'local');
                if ($disk->exists($file->file_path)) {
                    $file->file_name = $file->file_name ?: basename($file->file_path);
                    $file->file_size = $disk->size($file->file_path);
                    try {
                        $file->mime = $disk->mimeType($file->file_path) ?: $file->mime;
                    } catch (\Throwable $e) {
                        // non-fatal
                    }
                }
            }
        });
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(MaterialFolder::class, 'material_folder_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function courseMaterials(): HasMany
    {
        return $this->hasMany(CourseMaterial::class);
    }

    public function isFileKind(): bool
    {
        return in_array($this->kind, ['file', 'image']) && (bool) $this->file_path;
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

    public function extension(): string
    {
        return strtoupper(pathinfo($this->file_name ?: $this->file_path ?: '', PATHINFO_EXTENSION) ?: $this->kind);
    }

    public function icon(): string
    {
        return match ($this->kind) {
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

    /** For the admin file manager — a direct (auth-gated) download link. */
    public function adminDownloadUrl(): ?string
    {
        return $this->isFileKind() ? route('admin.library.download', $this) : $this->url;
    }
}
