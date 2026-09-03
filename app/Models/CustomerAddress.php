<?php

namespace App\Models;

use Database\Factories\CustomerAddressFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['user_id', 'label', 'recipient_name', 'phone', 'apartment', 'building', 'street', 'city', 'state', 'country', 'postal_code', 'is_default'])]
class CustomerAddress extends Model
{
    /** @use HasFactory<CustomerAddressFactory> */
    use HasFactory, SoftDeletes;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return array<string, string|null> */
    public function toOrderAddress(): array
    {
        return [
            'recipient_name' => $this->recipient_name,
            'phone' => $this->phone,
            'apartment' => $this->apartment,
            'building' => $this->building,
            'street' => $this->street,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'postal_code' => $this->postal_code,
        ];
    }

    protected function casts(): array
    {
        return ['is_default' => 'boolean'];
    }
}
