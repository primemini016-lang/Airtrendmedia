<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskProof extends Model
{
    protected $fillable = [
        'user_id', 'task_id', 'comment', 'images', 'reject_note', 'status',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
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

    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'proof_id');
    }
}
