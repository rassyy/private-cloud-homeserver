<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Folder extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'parent_id',
        'name',
        'is_starred',
        'trashed_at',
    ];

    protected $casts = [
        'is_starred' => 'boolean',
        'trashed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Folder $folder) {
            $folder->uuid = $folder->uuid ?? Str::uuid()->toString();
        });
    }

    // ── Relationships ──

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    // ── Ancestor Chain (for Breadcrumbs) ──

    public function getAncestorsAttribute(): array
    {
        $ancestors = [];
        $current = $this->parent;

        while ($current) {
            array_unshift($ancestors, $current);
            $current = $current->parent;
        }

        return $ancestors;
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

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }
}
