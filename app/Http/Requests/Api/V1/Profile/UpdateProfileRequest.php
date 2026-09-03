<?php

namespace App\Http\Requests\Api\V1\Profile;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users')->ignore($this->user())],
            'phone' => ['sometimes', 'nullable', 'string', 'regex:/^\+?[0-9]{8,15}$/'],
            'current_password' => ['required_with:password', 'current_password'],
            'password' => ['sometimes', 'required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
