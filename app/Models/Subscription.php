<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'xendit_invoice_id',
        'external_id',
        'status',
        'amount',
        'duration_days',
        'starts_at',
        'expires_at',
        'payment_method',
        'payment_channel',
        'xendit_payload',
    ];

    protected $casts = [
        'starts_at'       => 'datetime',
        'expires_at'      => 'datetime',
        'xendit_payload'  => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'paid'
            && $this->expires_at !== null
            && $this->expires_at->isFuture();
    }
}
