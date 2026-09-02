<?php

namespace App\Http\Requests\Api\V1\Checkout;

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
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'regex:/^\+?[0-9]{8,15}$/'],
            'shipping_address' => ['required', 'array'],
            'shipping_address.apartment' => ['nullable', 'string', 'max:100'],
            'shipping_address.building' => ['required', 'string', 'max:100'],
            'shipping_address.street' => ['required', 'string', 'max:255'],
            'shipping_address.city' => ['required', 'string', 'max:100'],
            'shipping_address.state' => ['nullable', 'string', 'max:100'],
            'shipping_address.country' => ['required', 'string', 'size:2'],
            'shipping_address.postal_code' => ['nullable', 'string', 'max:20'],
            'billing_address' => ['nullable', 'array'],
            'payment_method' => ['required', Rule::in(['paymob'])],
            'customer_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
