<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogCategory extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'color', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean'];

    protected static function booted(): void
    {
        static::creating(function (BlogCategory $cat) {
            if (empty($cat->slug)) {
                $cat->slug = Str::slug($cat->name) . '-' . Str::lower(Str::random(5));
            }
        });
    }

    public function posts()
    {
        return $this->hasMany(BlogPost::class, 'category_id');
    }

    public function publishedPosts()
    {
        return $this->hasMany(BlogPost::class, 'category_id')->where('status', 'published')->where('published_at', '<=', now());
    }
}
