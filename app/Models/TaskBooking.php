<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskBooking extends Model
{
    protected $fillable = ['user_id', 'task_id', 'expire_in'];

    protected function casts(): array
    {
        return [
            'expire_in' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function isExpired(): bool
    {
        return $this->expire_in && now()->gt($this->expire_in);
    }
}
