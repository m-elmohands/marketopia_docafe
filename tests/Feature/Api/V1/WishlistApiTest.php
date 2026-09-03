<?php

namespace Tests\Feature\Api\V1;

use App\Enums\CatalogStatus;
use App\Models\Product;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class WishlistApiTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_wishlist_requires_authentication(): void
    {
        $product = $this->product();

        $this->getJson('/api/v1/wishlist')->assertUnauthorized();
        $this->postJson('/api/v1/wishlist/items', ['product_id' => $product->id])->assertUnauthorized();
        $this->deleteJson('/api/v1/wishlist/items/'.$product->id)->assertUnauthorized();
    }

    public function test_customer_can_add_list_remove_and_restore_a_product_without_duplicates(): void
    {
        $user = User::factory()->create();
        $product = $this->product('Flat White');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/wishlist/items', ['product_id' => $product->id])
            ->assertCreated()
            ->assertJsonPath('data.product.id', $product->id)
            ->assertJsonPath('data.product.name', 'Flat White');

        $this->postJson('/api/v1/wishlist/items', ['product_id' => $product->id])->assertOk();
        $this->assertDatabaseCount('wishlist_items', 1);

        $this->getJson('/api/v1/wishlist?sort=oldest&search=Flat&per_page=10')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.product.id', $product->id)
            ->assertJsonPath('meta.per_page', 10);

        $this->deleteJson('/api/v1/wishlist/items/'.$product->id)
            ->assertOk()
            ->assertJsonPath('message', 'Product removed from wishlist.');
        $this->assertSoftDeleted('wishlist_items', ['user_id' => $user->id, 'product_id' => $product->id]);

        $this->postJson('/api/v1/wishlist/items', ['product_id' => $product->id])->assertOk();
        $this->assertDatabaseCount('wishlist_items', 1);
        $this->assertNotNull(WishlistItem::query()->whereBelongsTo($user)->first());
    }

    public function test_customer_cannot_add_an_unavailable_product(): void
    {
        $user = User::factory()->create();
        $product = $this->product(status: CatalogStatus::Draft);

        $this->actingAs($user, 'sanctum')
            ->withHeader('Accept-Language', 'ar')
            ->postJson('/api/v1/wishlist/items', ['product_id' => $product->id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['product_id'])
            ->assertJsonPath('errors.product_id.0', 'المنتج المحدد غير متاح.');

        $this->assertDatabaseCount('wishlist_items', 0);
    }

    public function test_customer_cannot_remove_another_customers_wishlist_item(): void
    {
        $owner = User::factory()->create();
        $otherCustomer = User::factory()->create();
        $product = $this->product();
        WishlistItem::query()->create(['user_id' => $owner->id, 'product_id' => $product->id]);

        $this->actingAs($otherCustomer, 'sanctum')
            ->deleteJson('/api/v1/wishlist/items/'.$product->id)
            ->assertNotFound();

        $this->assertDatabaseHas('wishlist_items', [
            'user_id' => $owner->id,
            'product_id' => $product->id,
            'deleted_at' => null,
        ]);
    }

    private function product(string $name = 'Espresso', CatalogStatus $status = CatalogStatus::Active): Product
    {
        $product = Product::factory()->create(['status' => $status]);
        $product->translateOrNew('en')->fill(['name' => $name]);
        $product->translateOrNew('ar')->fill(['name' => 'قهوة']);
        $product->save();

        return $product;
    }
}
