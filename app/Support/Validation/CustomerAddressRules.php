<?php

namespace App\Support\Validation;

final class CustomerAddressRules
{
    /** @return array<string, list<string>> */
    public static function rules(bool $partial = false): array
    {
        $rules = [
            'label' => ['required', 'string', 'max:100'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9]{8,15}$/'],
            'apartment' => ['nullable', 'string', 'max:100'],
            'building' => ['required', 'string', 'max:100'],
            'street' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'country' => ['required', 'string', 'size:2'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'is_default' => ['sometimes', 'boolean'],
        ];

        if (! $partial) {
            return $rules;
        }

        return collect($rules)->map(fn (array $fieldRules): array => [
            'sometimes',
            ...array_values(array_filter($fieldRules, fn (string $rule): bool => $rule !== 'required' && $rule !== 'sometimes')),
        ])->all();
    }
}
