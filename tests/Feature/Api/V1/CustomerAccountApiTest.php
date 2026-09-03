<?php

namespace Tests\Feature\Api\V1;

use App\Enums\CatalogStatus;
use App\Enums\PaymentMethod;
use App\Models\Cart;
use App\Models\CustomerAddress;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CustomerAccountApiTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_customer_can_update_profile_and_password(): void
    {
        $user = User::factory()->create(['password' => 'OldPassword123']);

        $this->actingAs($user, 'sanctum')
            ->patchJson('/api/v1/profile', [
                'name' => 'Updated Customer',
                'email' => 'updated@example.com',
                'phone' => '+201001112233',
                'current_password' => 'OldPassword123',
                'password' => 'NewPassword123',
                'password_confirmation' => 'NewPassword123',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Profile updated successfully.')
            ->assertJsonPath('data.phone', '+201001112233');

        $user->refresh();
        $this->assertSame('updated@example.com', $user->email);
        $this->assertTrue(Hash::check('NewPassword123', $user->password));
    }

    public function test_customer_can_manage_addresses_with_one_default_address(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $home = $this->postJson('/api/v1/addresses', $this->addressPayload('Home'))
            ->assertCreated()
            ->assertJsonPath('data.is_default', true)
            ->json('data.id');

        $work = $this->postJson('/api/v1/addresses', [
            ...$this->addressPayload('Work'),
            'is_default' => true,
        ])->assertCreated()->json('data.id');

        $this->assertDatabaseHas('customer_addresses', ['id' => $home, 'is_default' => false]);
        $this->assertDatabaseHas('customer_addresses', ['id' => $work, 'is_default' => true]);

        $this->patchJson('/api/v1/addresses/'.$home, ['city' => 'Giza'])
            ->assertOk()
            ->assertJsonPath('data.city', 'Giza');
        $this->getJson('/api/v1/addresses')->assertOk()->assertJsonCount(2, 'data');

        $this->deleteJson('/api/v1/addresses/'.$work)->assertOk();
        $this->assertSoftDeleted('customer_addresses', ['id' => $work]);
        $this->assertDatabaseHas('customer_addresses', ['id' => $home, 'is_default' => true]);
    }

    public function test_customer_cannot_access_another_customers_address(): void
    {
        $address = CustomerAddress::factory()->create();
        $otherCustomer = User::factory()->create();

        $this->actingAs($otherCustomer, 'sanctum')
            ->getJson('/api/v1/addresses/'.$address->id)
            ->assertForbidden();

        $this->deleteJson('/api/v1/addresses/'.$address->id)->assertForbidden();
        $this->assertDatabaseHas('customer_addresses', ['id' => $address->id, 'deleted_at' => null]);
    }

    public function test_customer_can_checkout_with_cash_on_delivery_and_a_saved_address(): void
    {
        Http::fake();
        $user = User::factory()->create();
        $address = CustomerAddress::factory()->for($user)->create(['is_default' => true]);
        $product = $this->product();
        $cart = Cart::query()->create(['user_id' => $user->id, 'currency' => 'EGP']);
        $cart->items()->create(['product_id' => $product->id, 'quantity' => 2]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/checkout', [
                'order_type' => 'takeaway',
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => $address->phone,
                'address_id' => $address->id,
                'payment_method' => PaymentMethod::CashOnDelivery->value,
            ])
            ->assertCreated()
            ->assertJsonPath('data.order.payment_method', 'cash_on_delivery')
            ->assertJsonPath('data.order.shipping_address.street', $address->street)
            ->assertJsonPath('data.checkout_url', null);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'payment_method' => 'cash_on_delivery',
            'payment_status' => 'pending',
        ]);
        $this->assertDatabaseCount('payment_intentions', 0);
        $this->assertDatabaseCount('cart_items', 0);
        Http::assertNothingSent();
    }

    private function addressPayload(string $label): array
    {
        return [
            'label' => $label,
            'recipient_name' => 'Coffee Customer',
            'phone' => '+201001234567',
            'building' => '10',
            'street' => 'Tahrir Street',
            'city' => 'Cairo',
            'country' => 'EG',
        ];
    }

    private function product(): Product
    {
        $product = Product::factory()->create([
            'status' => CatalogStatus::Active,
            'track_inventory' => true,
            'stock_quantity' => 5,
        ]);
        $product->translateOrNew('en')->fill(['name' => 'Latte']);
        $product->translateOrNew('ar')->fill(['name' => 'لاتيه']);
        $product->save();

        return $product;
    }
}
