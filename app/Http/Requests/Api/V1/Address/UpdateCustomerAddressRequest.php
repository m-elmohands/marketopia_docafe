<?php

namespace App\Http\Requests\Api\V1\Address;

use App\Models\CustomerAddress;
use App\Support\Validation\CustomerAddressRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerAddressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $address = $this->route('address');

        return $address instanceof CustomerAddress && ($this->user()?->can('update', $address) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return CustomerAddressRules::rules(partial: true);
    }
}
