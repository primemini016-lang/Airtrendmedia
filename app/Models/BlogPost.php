<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    protected $fillable = [
        'author_id', 'category_id', 'title', 'slug', 'content', 'excerpt',
        'featured_image', 'gallery', 'meta_description', 'meta_keywords',
        'status', 'is_featured', 'published_at', 'views_count', 'likes_count',
        'comments_count', 'shares_count', 'reading_time',
    ];

    protected $casts = [
        'published_at'  => 'datetime',
        'is_featured'   => 'boolean',
        'gallery'       => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (BlogPost $post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title) . '-' . Str::lower(Str::random(6));
            }
            if (empty($post->excerpt)) {
                $post->excerpt = Str::limit(strip_tags($post->content), 160);
            }
            // estimate reading time: ~200 words per minute
            $wordCount = str_word_count(strip_tags($post->content));
            $post->reading_time = max(1, (int) ceil($wordCount / 200));
        });

        static::updating(function (BlogPost $post) {
            if ($post->isDirty('content') && empty($post->excerpt)) {
                $post->excerpt = Str::limit(strip_tags($post->content), 160);
            }
        });
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(BlogComment::class)->whereNull('parent_id')->where('is_approved', true)->latest();
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(BlogComment::class)->latest();
    }

    public function likes(): HasMany
    {
        return $this->hasMany(BlogLike::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(BlogView::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(BlogRate::class);
    }

    public function shares(): HasMany
    {
        return $this->hasMany(BlogShare::class);
    }

    public function isLikedBy(?User $user): bool
    {
        if (!$user) return false;
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    public function isRatedBy(?User $user): ?int
    {
        if (!$user) return null;
        $rate = $this->ratings()->where('user_id', $user->id)->first();
        return $rate?->rating;
    }

    public function averageRating(): float
    {
        return round($this->ratings()->avg('rating') ?? 0, 1);
    }

    public function ratingsCount(): int
    {
        return $this->ratings()->count();
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')->where('published_at', '<=', now());
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'LIKE', "%{$term}%")
              ->orWhere('content', 'LIKE', "%{$term}%")
              ->orWhere('excerpt', 'LIKE', "%{$term}%")
              ->orWhere('meta_keywords', 'LIKE', "%{$term}%");
        });
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
