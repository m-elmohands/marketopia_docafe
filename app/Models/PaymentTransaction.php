<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentTransaction extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'payment_intention_id',
        'paymob_transaction_id',
        'paymob_order_id',
        'parent_transaction_id',
        'integration_id',
        'status',
        'amount_cents',
        'currency',
        'source_type',
        'source_subtype',
        'masked_pan',
        'is_success',
        'is_pending',
        'is_authorized',
        'is_captured',
        'is_voided',
        'is_refunded',
        'is_3d_secure',
        'error_code',
        'error_message',
        'provider_data',
        'processed_at',
    ];

    public function intention(): BelongsTo
    {
        return $this->belongsTo(PaymentIntention::class, 'payment_intention_id');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(PaymentRefund::class);
    }

    public function webhooks(): HasMany
    {
        return $this->hasMany(PaymentWebhook::class);
    }

    protected function casts(): array
    {
        return [
            'is_success' => 'boolean',
            'is_pending' => 'boolean',
            'is_authorized' => 'boolean',
            'is_captured' => 'boolean',
            'is_voided' => 'boolean',
            'is_refunded' => 'boolean',
            'is_3d_secure' => 'boolean',
            'provider_data' => 'array',
            'processed_at' => 'datetime',
        ];
    }
}
