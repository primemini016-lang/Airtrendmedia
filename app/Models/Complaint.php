<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $fillable = [
        'proof_id', 'user_id', 'user2_id', 'details', 'reply', 'status',
    ];

    public function proof()
    {
        return $this->belongsTo(TaskProof::class, 'proof_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function employer()
    {
        return $this->belongsTo(User::class, 'user2_id');
    }
}
