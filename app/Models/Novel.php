<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Novel extends Model
{
    use HasFactory;

    protected $fillable = [
        'author_id',
        'author_name',
        'title',
        'slug',
        'synopsis',
        'cover_image',
        'view_count',
        'status'
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(NovelReport::class);
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }

    public function getAuthorNameAttribute(): string
    {
        return $this->attributes['author_name'] ?? $this->author?->name ?? 'Unknown Author';
    }

    /**
     * Scope: sort novels by the given sort key.
     * Supported: new_release, most_popular, top_rated, recently_updated.
     */
    public function scopeSortBy(Builder $query, string $sort): Builder
    {
        return match ($sort) {
            'most_popular' => $query->orderBy('view_count', 'desc'),

            'top_rated' => $query->withCount([
                'votes as upvotes'   => fn($q) => $q->where('type', 'upvote'),
                'votes as downvotes' => fn($q) => $q->where('type', 'downvote'),
            ])->orderByRaw('(upvotes - downvotes) DESC'),

            'recently_updated' => $query->orderBy(
                Chapter::select('updated_at')
                    ->whereColumn('novel_id', 'novels.id')
                    ->latest()
                    ->limit(1),
                'desc'
            )->orderBy('updated_at', 'desc'),

            default => $query->orderBy('created_at', 'desc'), // new_release
        };
    }

    /**
     * Scope: filter novels by status for admin panel.
     * 'reported' filters novels that have at least one report.
     * 'all' applies no filter.
     */
    public function scopeFilterByStatus(Builder $query, string $status): Builder
    {
        return match ($status) {
            'reported' => $query->whereHas('reports'),
            'all'      => $query,
            default    => $query->where('status', $status),
        };
    }
}
