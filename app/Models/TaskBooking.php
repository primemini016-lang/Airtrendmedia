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

    /**
     * Proofs submitted by this booking's user for this booking's task.
     *
     * A TaskProof is linked to a booking by the combination of user_id and
     * task_id (the worker who booked the task submits a proof for that task).
     * This relationship powers the worker's "My Bookings" status filters.
     */
    public function proofs()
    {
        return $this->hasMany(TaskProof::class, 'user_id', 'user_id')
            ->whereColumn('task_id', 'task_id');
    }

    public function isExpired(): bool
    {
        return $this->expire_in && now()->gt($this->expire_in);
    }
}
