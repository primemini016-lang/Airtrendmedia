<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    protected $fillable = [
        'title', 'position', 'type', 'content', 'link_url', 'image_path',
        'active', 'sort_order', 'starts_at', 'ends_at',
    ];

    protected $casts = [
        'active'    => 'boolean',
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->orderBy('sort_order');
    }

    public function scopeAtPosition($query, string $position)
    {
        return $query->where('position', $position);
    }

    /**
     * Render an ad as HTML for display in views.
     */
    public static function render(self $ad): string
    {
        if ($ad->type === 'image' && $ad->image_path) {
            $img = '<img src="' . storage_asset($ad->image_path) . '" alt="' . e($ad->title) . '" style="max-width:100%;height:auto;border-radius:8px;">';
            if ($ad->link_url) {
                return '<a href="' . e($ad->link_url) . '" target="_blank" rel="noopener">' . $img . '</a>';
            }
            return $img;
        }

        if ($ad->type === 'html') {
            return $ad->content ?? '';
        }

        // text type
        $text = '<div style="padding:12px;background:#f0f7ff;border:1px solid #dbeafe;border-radius:8px;color:#1e40af;font-size:14px;">' . e($ad->content ?? '') . '</div>';
        if ($ad->link_url) {
            return '<a href="' . e($ad->link_url) . '" target="_blank" rel="noopener" style="text-decoration:none;">' . $text . '</a>';
        }
        return $text;
    }
}
