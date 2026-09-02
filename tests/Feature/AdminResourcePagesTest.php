<?php

namespace Tests\Feature;

use App\Filament\Widgets\CommerceOverview;
use App\Models\Order;
use App\Models\PaymentIntention;
use App\Models\PaymentRefund;
use App\Models\PaymentTransaction;
use App\Models\User;
use Database\Seeders\AuthorizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminResourcePagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_render_ecommerce_management_pages(): void
    {
        $this->seed(AuthorizationSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $order = Order::create([
            'order_number' => 'ORD-ADMIN-1',
            'customer_name' => 'Admin Test Customer',
            'customer_email' => 'customer@example.com',
            'shipping_address' => ['city' => 'Cairo', 'country' => 'EG'],
            'currency' => 'EGP',
            'subtotal' => 10_000,
            'grand_total' => 10_000,
        ]);
        $order->items()->create([
            'product_name' => 'Coffee',
            'sku' => 'COFFEE-1',
            'unit_price' => 10_000,
            'quantity' => 1,
            'line_total' => 10_000,
        ]);

        $intention = PaymentIntention::create([
            'order_id' => $order->id,
            'special_reference' => 'ORD-ADMIN-1-PAY-1',
            'amount_cents' => 10_000,
            'currency' => 'EGP',
        ]);
        $transaction = PaymentTransaction::create([
            'payment_intention_id' => $intention->id,
            'paymob_transaction_id' => 'TX-ADMIN-1',
            'amount_cents' => 10_000,
            'currency' => 'EGP',
        ]);
        $refund = PaymentRefund::create([
            'payment_transaction_id' => $transaction->id,
            'amount_cents' => 2_000,
            'currency' => 'EGP',
        ]);

        $this->actingAs($admin);

        foreach ([
            '/admin/profile',
            '/admin/products',
            '/admin/products/create',
            '/admin/categories',
            '/admin/categories/create',
            '/admin/articles',
            '/admin/articles/create',
            '/admin/settings',
            '/admin/settings/create',
            '/admin/orders',
            "/admin/orders/{$order->id}",
            '/admin/payment-transactions',
            "/admin/payment-transactions/{$transaction->id}",
            '/admin/payment-refunds',
            "/admin/payment-refunds/{$refund->id}",
            '/admin/users',
            '/admin/users/create',
        ] as $uri) {
            $this->get($uri)->assertOk();
        }

        Livewire::test(CommerceOverview::class)->assertOk();
    }
}
