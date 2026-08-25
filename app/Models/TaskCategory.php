<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskCategory extends Model
{
    protected $fillable = [
        'parent_id', 'name', 'slug', 'icon', 'color', 'price', 'min_amount',
        'active', 'position',
    ];

    protected function casts(): array
    {
        return [
            'price'      => 'decimal:2',
            'min_amount' => 'integer',
            'active'     => 'boolean',
        ];
    }

    public function parent()
    {
        return $this->belongsTo(TaskCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(TaskCategory::class, 'parent_id')->orderBy('position');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'category_id');
    }
}
