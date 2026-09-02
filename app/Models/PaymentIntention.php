<?php

namespace App\Models;

use App\Enums\Currency;
use App\Enums\PaymentProcessingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentIntention extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'order_id',
        'special_reference',
        'paymob_intention_id',
        'status',
        'amount_cents',
        'currency',
        'payment_methods',
        'is_live',
        'provider_data',
        'expires_at',
        'paid_at',
        'failed_at',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    protected function casts(): array
    {
        return [
            'status' => PaymentProcessingStatus::class,
            'currency' => Currency::class,
            'payment_methods' => 'array',
            'is_live' => 'boolean',
            'provider_data' => 'array',
            'expires_at' => 'datetime',
            'paid_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }
}
