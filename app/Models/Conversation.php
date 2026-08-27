<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $fillable = ['name', 'is_group', 'group_avatar', 'created_by', 'last_message_at'];

    protected $casts = [
        'is_group'       => 'boolean',
        'last_message_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->latest();
    }

    public function lastMessage()
    {
        return $this->hasOne(ChatMessage::class)->latestOfMany();
    }

    public function otherParticipant(?User $user)
    {
        if (!$user || $this->is_group) return null;
        return $this->participants()->where('user_id', '!=', $user->id)->first()?->user;
    }
}
