<?php

namespace Tests\Feature\Api\V1;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\CoffeeCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class EcommerceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register_manage_cart_and_create_an_idempotent_paymob_checkout(): void
    {
        $this->seed(CoffeeCatalogSeeder::class);
        $product = Product::query()->firstOrFail();
        $product->update(['track_inventory' => true, 'stock_quantity' => 5]);

        config()->set('services.paymob.secret_key', 'secret-key');
        config()->set('services.paymob.public_key', 'public-key');
        config()->set('services.paymob.integration_ids', ['12345']);
        config()->set('services.paymob.notification_url', 'https://example.test/api/v1/payments/paymob/webhook');
        config()->set('services.paymob.redirection_url', 'https://example.test/payment/result');
        Http::fake([
            '*/v1/intention/' => Http::response([
                'id' => 'intention-1',
                'client_secret' => 'client-secret',
                'special_reference' => 'provider-reference',
            ]),
        ]);

        $register = $this->postJson('/api/v1/auth/register', [
            'name' => 'Coffee Customer',
            'email' => 'customer@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'device_name' => 'phpunit',
        ])->assertCreated();

        $token = $register->json('data.token');
        $headers = ['Authorization' => 'Bearer '.$token, 'Accept-Language' => 'ar'];

        $this->withHeaders($headers)->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ])->assertCreated()->assertJsonPath('data.items_count', 2);

        $payload = [
            'idempotency_key' => (string) Str::uuid(),
            'customer_name' => 'Ahmed Ali',
            'customer_email' => 'customer@example.com',
            'customer_phone' => '+201001234567',
            'shipping_address' => [
                'building' => '10',
                'street' => 'Tahrir Street',
                'city' => 'Cairo',
                'country' => 'EG',
            ],
            'payment_method' => 'paymob',
        ];

        $checkout = $this->withHeaders($headers)->postJson('/api/v1/checkout', $payload)
            ->assertCreated()
            ->assertJsonPath('data.order.payment_status', 'pending');

        $this->assertStringContainsString('clientSecret=client-secret', $checkout->json('data.checkout_url'));
        $this->assertSame(3, $product->fresh()->stock_quantity);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseMissing('cart_items', ['product_id' => $product->id]);

        $this->withHeaders($headers)->postJson('/api/v1/checkout', $payload)
            ->assertCreated()
            ->assertJsonPath('data.order.id', $checkout->json('data.order.id'));

        $this->assertDatabaseCount('orders', 1);
        Http::assertSentCount(1);
    }

    public function test_paymob_webhook_rejects_bad_hmac_and_marks_a_matching_order_paid(): void
    {
        config()->set('services.paymob.hmac_secret', 'hmac-secret');
        $user = User::factory()->create();
        $order = Order::create([
            'order_number' => 'DC-TEST-1',
            'user_id' => $user->id,
            'status' => 'pending',
            'payment_status' => 'pending',
            'fulfillment_status' => 'unfulfilled',
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'shipping_address' => ['city' => 'Cairo'],
            'currency' => 'EGP',
            'subtotal' => 10000,
            'grand_total' => 10000,
            'placed_at' => now(),
        ]);
        $order->paymentIntentions()->create([
            'special_reference' => $order->order_number,
            'paymob_intention_id' => 'intent-1',
            'status' => 'pending',
            'amount_cents' => 10000,
            'currency' => 'EGP',
        ]);

        $object = [
            'amount_cents' => 10000, 'created_at' => '2026-09-02T12:00:00', 'currency' => 'EGP',
            'error_occured' => false, 'has_parent_transaction' => false, 'id' => 98765,
            'integration_id' => 12345, 'is_3d_secure' => true, 'is_auth' => false,
            'is_capture' => false, 'is_refunded' => false, 'is_standalone_payment' => true,
            'is_voided' => false, 'order' => ['id' => 55, 'merchant_order_id' => $order->order_number],
            'owner' => 1, 'pending' => false,
            'source_data' => ['pan' => '2346', 'sub_type' => 'MasterCard', 'type' => 'card'],
            'success' => true,
        ];

        $this->postJson('/api/v1/payments/paymob/webhook?hmac=invalid', ['type' => 'TRANSACTION', 'obj' => $object])
            ->assertUnauthorized();

        $this->postJson('/api/v1/payments/paymob/webhook?hmac='.$this->hmac($object), ['type' => 'TRANSACTION', 'obj' => $object])
            ->assertOk();

        $this->assertSame(PaymentStatus::Paid, $order->fresh()->payment_status);
        $this->assertNotNull($order->fresh()->paid_at);
        $this->assertDatabaseHas('payment_transactions', ['paymob_transaction_id' => '98765', 'is_success' => true]);
    }

    public function test_catalog_is_localized_and_draft_products_are_hidden(): void
    {
        $this->seed(CoffeeCatalogSeeder::class);
        Product::query()->firstOrFail()->update(['status' => 'draft']);

        $response = $this->withHeader('Accept-Language', 'ar')->getJson('/api/v1/products?per_page=50')
            ->assertOk();

        $this->assertCount(11, $response->json('data'));
        $this->assertNotEmpty($response->json('data.0.name'));
    }

    private function hmac(array $object): string
    {
        $fields = [
            'amount_cents', 'created_at', 'currency', 'error_occured', 'has_parent_transaction',
            'id', 'integration_id', 'is_3d_secure', 'is_auth', 'is_capture', 'is_refunded',
            'is_standalone_payment', 'is_voided', 'order.id', 'owner', 'pending',
            'source_data.pan', 'source_data.sub_type', 'source_data.type', 'success',
        ];

        $data = collect($fields)->map(function (string $field) use ($object): string {
            $value = Arr::get($object, $field);

            return is_bool($value) ? ($value ? 'true' : 'false') : (string) $value;
        })->implode('');

        return hash_hmac('sha512', $data, 'hmac-secret');
    }
}
