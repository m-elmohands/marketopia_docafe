<?php

namespace App\Http\Requests\Api\V1\Checkout;

use App\Enums\OrderType;
use App\Enums\PaymentMethod;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_type' => ['required', Rule::enum(OrderType::class)],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'regex:/^\+?[0-9]{8,15}$/'],
            'address_id' => [
                'nullable',
                'integer',
                'required_without:shipping_address',
                Rule::exists('customer_addresses', 'id')->where(fn (Builder $query): Builder => $query
                    ->where('user_id', $this->user()->id)
                    ->whereNull('deleted_at')),
            ],
            'shipping_address' => ['nullable', 'required_without:address_id', 'array'],
            'shipping_address.apartment' => ['nullable', 'string', 'max:100'],
            'shipping_address.building' => ['required_with:shipping_address', 'string', 'max:100'],
            'shipping_address.street' => ['required_with:shipping_address', 'string', 'max:255'],
            'shipping_address.city' => ['required_with:shipping_address', 'string', 'max:100'],
            'shipping_address.state' => ['nullable', 'string', 'max:100'],
            'shipping_address.country' => ['required_with:shipping_address', 'string', 'size:2'],
            'shipping_address.postal_code' => ['nullable', 'string', 'max:20'],
            'billing_address' => ['nullable', 'array'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'customer_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
