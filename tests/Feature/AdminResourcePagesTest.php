<?php

namespace Tests\Feature;

use App\Filament\Resources\Articles\ArticleResource;
use App\Filament\Resources\Articles\Pages\ListArticles;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Settings\Pages\ListSettings;
use App\Filament\Resources\Settings\SettingResource;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Widgets\CommerceOverview;
use App\Models\Order;
use App\Models\PaymentIntention;
use App\Models\PaymentRefund;
use App\Models\PaymentTransaction;
use App\Models\User;
use Database\Seeders\AuthorizationSeeder;
use Filament\Actions\Action;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminResourcePagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_switch_the_panel_between_english_and_arabic(): void
    {
        $this->seed(AuthorizationSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->from('/admin')
            ->get(route('admin.locale', ['locale' => 'ar']))
            ->assertRedirect('/admin')
            ->assertSessionHas('filament_locale', 'ar');

        $this->get('/admin')
            ->assertOk()
            ->assertSee('dir="rtl"', false)
            ->assertSee('الكتالوج');

        $this->get(route('admin.locale', ['locale' => 'fr']))->assertNotFound();
    }

    public function test_create_and_edit_flows_are_configured_as_modal_actions(): void
    {
        $this->seed(AuthorizationSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin);

        foreach ([
            ArticleResource::class,
            CategoryResource::class,
            ProductResource::class,
            SettingResource::class,
            UserResource::class,
        ] as $resource) {
            $this->assertFalse($resource::hasPage('create'));
            $this->assertFalse($resource::hasPage('edit'));
        }

        foreach ([
            ListArticles::class,
            ListCategories::class,
            ListProducts::class,
            ListSettings::class,
            ListUsers::class,
        ] as $page) {
            Livewire::test($page)
                ->assertActionExists('create', fn (Action $action): bool => $action->getUrl() === null);
        }
    }

    public function test_users_resource_excludes_the_authenticated_admin(): void
    {
        $this->seed(AuthorizationSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $managedUser = User::factory()->create();

        $this->actingAs($admin);

        $visibleUserIds = UserResource::getEloquentQuery()->pluck('id');

        $this->assertFalse($visibleUserIds->contains($admin->id));
        $this->assertTrue($visibleUserIds->contains($managedUser->id));
    }

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
            '/admin/categories',
            '/admin/articles',
            '/admin/settings',
            '/admin/orders',
            "/admin/orders/{$order->id}",
            '/admin/payment-transactions',
            "/admin/payment-transactions/{$transaction->id}",
            '/admin/payment-refunds',
            "/admin/payment-refunds/{$refund->id}",
            '/admin/users',
        ] as $uri) {
            $this->get($uri)->assertOk();
        }

        Livewire::test(CommerceOverview::class)->assertOk();
    }
}
