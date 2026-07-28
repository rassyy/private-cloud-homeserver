<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'folder_id',
        'name',
        'storage_path',
        'mime_type',
        'size',
        'extension',
        'is_starred',
        'trashed_at',
        'last_accessed_at',
    ];

    protected $casts = [
        'size' => 'integer',
        'is_starred' => 'boolean',
        'trashed_at' => 'datetime',
        'last_accessed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (File $file) {
            $file->uuid = $file->uuid ?? Str::uuid()->toString();
        });
    }

    // ── Relationships ──

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    // ── Accessors ──

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 1) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }

        return $bytes . ' B';
    }

    public function getIconAttribute(): string
    {
        return match (true) {
            str_starts_with($this->mime_type, 'image/') => 'image',
            str_starts_with($this->mime_type, 'video/') => 'movie',
            str_starts_with($this->mime_type, 'audio/') => 'audio_file',
            $this->mime_type === 'application/pdf' => 'picture_as_pdf',
            str_contains($this->mime_type, 'spreadsheet') || str_contains($this->mime_type, 'csv') => 'table_chart',
            str_contains($this->mime_type, 'presentation') => 'slideshow',
            str_contains($this->mime_type, 'document') || str_contains($this->mime_type, 'text') => 'description',
            str_contains($this->mime_type, 'zip') || str_contains($this->mime_type, 'archive') || str_contains($this->mime_type, 'compressed') => 'folder_zip',
            default => 'draft',
        };
    }

    public function getIconColorAttribute(): string
    {
        return match (true) {
            str_starts_with($this->mime_type, 'image/') => 'text-orange-500',
            str_starts_with($this->mime_type, 'video/') => 'text-purple-500',
            str_starts_with($this->mime_type, 'audio/') => 'text-pink-500',
            $this->mime_type === 'application/pdf' => 'text-red-500',
            str_contains($this->mime_type, 'spreadsheet') || str_contains($this->mime_type, 'csv') => 'text-green-500',
            str_contains($this->mime_type, 'presentation') => 'text-yellow-500',
            str_contains($this->mime_type, 'document') || str_contains($this->mime_type, 'text') => 'text-blue-500',
            default => 'text-slate-400',
        };
    }

    // ── Scopes ──

    public function scopeNotTrashed($query)
    {
        return $query->whereNull('trashed_at');
    }

    public function scopeTrashed($query)
    {
        return $query->whereNotNull('trashed_at');
    }

    public function scopeStarred($query)
    {
        return $query->where('is_starred', true);
    }
}
