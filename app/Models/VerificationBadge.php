<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class VerificationBadge extends Model
{
    protected $fillable = [
        'user_id', 'verifiable_type', 'verifiable_id',
        'full_name', 'government_id_path', 'verification_code', 'category',
        'monthly_fee', 'valid_from', 'valid_until',
        'id_type', 'id_number', 'document_path', 'selfie_path',
        'status', 'rejection_reason', 'payment_reference',
        'amount_paid', 'paid_at', 'expired_at',
        'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'paid_at'        => 'datetime',
        'valid_from'     => 'datetime',
        'valid_until'    => 'datetime',
        'expired_at'     => 'datetime',
        'reviewed_at'    => 'datetime',
        'amount_paid'    => 'decimal:2',
        'monthly_fee'    => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }

    public function governmentIdUrl(): ?string
    {
        return $this->government_id_path ? asset('storage/' . $this->government_id_path) : null;
    }

    public function isActive(): bool
    {
        return $this->status === 'verified'
            && $this->valid_until
            && $this->valid_until->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->valid_until
            && $this->valid_until->isPast();
    }

    public function daysLeft(): int
    {
        if (!$this->valid_until) return 0;
        return max(0, now()->startOfDay()->diffInDays($this->valid_until, false));
    }
}
