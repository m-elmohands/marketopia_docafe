<?php

namespace Database\Factories;

use App\Enums\CatalogStatus;
use App\Enums\Currency;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sku' => fake()->unique()->bothify('COFFEE-####'),
            'status' => CatalogStatus::Active,
            'price' => fake()->numberBetween(5_000, 30_000),
            'currency' => Currency::EGP,
            'track_inventory' => false,
            'stock_quantity' => 0,
            'low_stock_threshold' => 5,
            'is_featured' => false,
            'published_at' => now(),
        ];
    }
}
