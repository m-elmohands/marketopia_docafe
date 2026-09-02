<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentWebhook extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'payment_transaction_id',
        'fingerprint',
        'event_type',
        'paymob_transaction_id',
        'hmac_verified',
        'status',
        'processing_attempts',
        'payload',
        'processing_error',
        'received_at',
        'processed_at',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class, 'payment_transaction_id');
    }

    protected function casts(): array
    {
        return [
            'hmac_verified' => 'boolean',
            'payload' => 'array',
            'received_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }
}
