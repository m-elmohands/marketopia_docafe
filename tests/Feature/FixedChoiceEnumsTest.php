<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Enums\CatalogStatus;
use App\Enums\Currency;
use App\Enums\FulfillmentStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentProcessingStatus;
use App\Enums\PaymentStatus;
use App\Enums\SettingType;
use App\Models\Article;
use App\Models\Category;
use App\Models\Order;
use App\Models\PaymentRefund;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FixedChoiceEnumsTest extends TestCase
{
    use RefreshDatabase;

    public function test_models_cast_fixed_choice_columns_to_enums(): void
    {
        $product = Product::create([
            'sku' => 'ENUM-COFFEE-1',
            'status' => CatalogStatus::Active,
            'price' => 7_500,
            'currency' => Currency::EGP,
        ]);

        $order = Order::create([
            'order_number' => 'ENUM-ORDER-1',
            'status' => OrderStatus::Processing,
            'payment_status' => PaymentStatus::Paid,
            'fulfillment_status' => FulfillmentStatus::Processing,
            'customer_name' => 'Enum Customer',
            'customer_email' => 'enum@example.com',
            'shipping_address' => ['city' => 'Cairo'],
            'currency' => Currency::EGP,
            'subtotal' => 7_500,
            'grand_total' => 7_500,
        ]);

        $this->assertSame(CatalogStatus::Active, $product->fresh()->status);
        $this->assertSame(Currency::EGP, $product->fresh()->currency);
        $this->assertSame(OrderStatus::Processing, $order->fresh()->status);
        $this->assertSame(OrderType::Takeaway, $order->fresh()->order_type);
        $this->assertSame(PaymentStatus::Paid, $order->fresh()->payment_status);
        $this->assertSame(FulfillmentStatus::Processing, $order->fresh()->fulfillment_status);
        $this->assertSame(CatalogStatus::Archived, (new Category(['status' => 'archived']))->status);
        $this->assertSame(ArticleStatus::Published, (new Article(['status' => 'published']))->status);
        $this->assertSame(SettingType::Json, (new Setting(['type' => 'json']))->type);
        $this->assertSame(
            PaymentProcessingStatus::Succeeded,
            (new PaymentTransaction(['status' => 'succeeded']))->status,
        );
        $this->assertSame(
            PaymentProcessingStatus::Failed,
            (new PaymentRefund(['status' => 'failed']))->status,
        );
    }

    public function test_database_rejects_values_outside_the_allowed_enum_cases(): void
    {
        $this->expectException(QueryException::class);

        DB::table('products')->insert([
            'sku' => 'INVALID-STATUS-1',
            'status' => 'not-a-status',
            'price' => 0,
            'currency' => Currency::EGP->value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_database_rejects_an_invalid_order_type(): void
    {
        $this->expectException(QueryException::class);

        DB::table('orders')->insert([
            'order_number' => 'INVALID-ORDER-TYPE-1',
            'order_type' => 'delivery',
            'customer_name' => 'Enum Customer',
            'customer_email' => 'enum@example.com',
            'shipping_address' => json_encode(['city' => 'Cairo'], JSON_THROW_ON_ERROR),
            'currency' => Currency::EGP->value,
            'subtotal' => 7_500,
            'grand_total' => 7_500,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
