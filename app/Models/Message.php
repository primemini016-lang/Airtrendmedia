<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['user_id', 'message', 'from_admin', 'seen'];

    protected function casts(): array
    {
        return [
            'from_admin' => 'boolean',
            'seen'       => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
