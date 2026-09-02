<?php

namespace App\Models;

use App\Enums\Currency;
use App\Enums\PaymentProcessingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentRefund extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'payment_transaction_id',
        'requested_by',
        'paymob_transaction_id',
        'status',
        'amount_cents',
        'currency',
        'reason',
        'provider_data',
        'processed_at',
        'failed_at',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class, 'payment_transaction_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    protected function casts(): array
    {
        return [
            'status' => PaymentProcessingStatus::class,
            'currency' => Currency::class,
            'provider_data' => 'array',
            'processed_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }
}
