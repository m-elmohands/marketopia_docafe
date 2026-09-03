<?php

namespace Database\Factories;

use App\Models\CustomerAddress;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerAddress>
 */
class CustomerAddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'label' => fake()->randomElement(['Home', 'Work']),
            'recipient_name' => fake()->name(),
            'phone' => '+201001234567',
            'apartment' => fake()->buildingNumber(),
            'building' => fake()->buildingNumber(),
            'street' => fake()->streetName(),
            'city' => 'Cairo',
            'state' => 'Cairo',
            'country' => 'EG',
            'postal_code' => '11511',
            'is_default' => false,
        ];
    }
}
