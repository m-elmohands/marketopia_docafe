<?php

namespace Tests\Feature;

use App\Enums\CatalogStatus;
use App\Enums\Currency;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\AuthorizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class CatalogDeletionRulesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('en');
    }

    public function test_category_cannot_be_deleted_while_it_contains_products(): void
    {
        [$admin, $product] = $this->adminAndProduct();
        $category = Category::create(['status' => CatalogStatus::Active]);
        $category->products()->attach($product);

        $this->actingAs($admin);

        $response = Gate::inspect('delete', $category);

        $this->assertTrue($response->denied());
        $this->assertSame('A category containing products cannot be deleted.', $response->message());
        $this->assertTrue(Gate::inspect('forceDelete', $category)->denied());

        Livewire::test(ListCategories::class)
            ->callTableBulkAction('delete', [$category])
            ->assertNotified('A category containing products cannot be deleted.');

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'deleted_at' => null]);

        try {
            $category->delete();
            $this->fail('The category deletion should have been blocked.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('category', $exception->errors());
        }

        $category->products()->detach();

        $this->assertTrue(Gate::allows('delete', $category));
        $this->assertTrue($category->delete());
    }

    public function test_product_cannot_be_deleted_after_it_is_added_to_an_order(): void
    {
        [$admin, $product] = $this->adminAndProduct();
        $order = Order::create([
            'order_number' => 'DELETE-RULE-1',
            'customer_name' => 'Deletion Test',
            'customer_email' => 'deletion@example.com',
            'shipping_address' => ['city' => 'Cairo'],
            'currency' => Currency::EGP,
            'subtotal' => 7_500,
            'grand_total' => 7_500,
        ]);
        $item = $order->items()->create([
            'product_id' => $product->id,
            'product_name' => 'Espresso',
            'sku' => $product->sku,
            'unit_price' => 7_500,
            'quantity' => 1,
            'line_total' => 7_500,
        ]);

        $this->actingAs($admin);

        $response = Gate::inspect('delete', $product);

        $this->assertTrue($response->denied());
        $this->assertSame('A product included in an order cannot be deleted.', $response->message());
        $this->assertTrue(Gate::inspect('forceDelete', $product)->denied());

        Livewire::test(ListProducts::class)
            ->callTableBulkAction('delete', [$product])
            ->assertNotified('A product included in an order cannot be deleted.');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'deleted_at' => null]);

        try {
            $product->delete();
            $this->fail('The product deletion should have been blocked.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('product', $exception->errors());
        }

        $item->delete();

        $this->assertTrue(Gate::allows('delete', $product));
        $this->assertTrue($product->delete());
    }

    /** @return array{User, Product} */
    private function adminAndProduct(): array
    {
        $this->seed(AuthorizationSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $product = Product::create([
            'sku' => fake()->unique()->bothify('COF-###'),
            'status' => CatalogStatus::Active,
            'price' => 7_500,
            'currency' => Currency::EGP,
        ]);

        return [$admin, $product];
    }
}
