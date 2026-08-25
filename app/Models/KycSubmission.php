<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KycSubmission extends Model
{
    protected $fillable = [
        'user_id', 'full_name', 'id_type', 'id_number', 'date_of_birth',
        'country', 'address', 'document_front', 'document_back', 'selfie',
        'status', 'rejection_reason', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'date_of_birth'  => 'date',
        'reviewed_at'    => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }

    public function documentFrontUrl(): ?string
    {
        return $this->document_front ? asset('storage/' . $this->document_front) : null;
    }

    public function documentBackUrl(): ?string
    {
        return $this->document_back ? asset('storage/' . $this->document_back) : null;
    }

    public function selfieUrl(): ?string
    {
        return $this->selfie ? asset('storage/' . $this->selfie) : null;
    }
}
