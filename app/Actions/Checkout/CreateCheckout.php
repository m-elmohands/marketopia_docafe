<?php

namespace App\Actions\Checkout;

use App\Enums\Currency;
use App\Enums\FulfillmentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\AuditLog;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\Checkout\CheckoutTotals;
use App\Services\Payments\PaymobClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class CreateCheckout
{
    public function __construct(
        private readonly PaymobClient $paymob,
        private readonly CheckoutTotals $totals,
    ) {}

    public function execute(User $user, array $data): array
    {
        $order = DB::transaction(function () use ($user, $data): Order {
            $cart = Cart::query()->where('user_id', $user->id)->with('items')->lockForUpdate()->first();

            if (! $cart || $cart->items->isEmpty()) {
                throw ValidationException::withMessages(['cart' => [__('Your cart is empty.')]]);
            }

            $products = Product::query()->with('translations')->whereKey($cart->items->pluck('product_id'))->lockForUpdate()->get()->keyBy('id');
            $subtotal = 0;

            foreach ($cart->items as $item) {
                $product = $products->get($item->product_id);
                if (! $product || ($product->track_inventory && $product->stock_quantity < $item->quantity)) {
                    throw ValidationException::withMessages(['cart' => [__('A cart item is no longer available in the requested quantity.')]]);
                }
                $subtotal += $product->price * $item->quantity;
            }

            $totals = $this->totals->calculate($subtotal);

            $order = Order::create([
                'order_number' => 'DC-'.now()->format('ymd').'-'.Str::upper(Str::random(8)),
                'idempotency_key' => (string) Str::uuid(),
                'user_id' => $user->id,
                'status' => OrderStatus::Pending,
                'payment_status' => PaymentStatus::Pending,
                'fulfillment_status' => FulfillmentStatus::Unfulfilled,
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'customer_phone' => $data['customer_phone'],
                'billing_address' => $data['billing_address'] ?? $data['shipping_address'],
                'shipping_address' => $data['shipping_address'],
                'currency' => Currency::EGP,
                ...$totals,
                'payment_method' => 'paymob',
                'customer_notes' => $data['customer_notes'] ?? null,
                'placed_at' => now(),
            ]);

            foreach ($cart->items as $item) {
                $product = $products->get($item->product_id);
                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->translate(app()->getLocale(), true)?->name ?? $product->sku,
                    'sku' => $product->sku,
                    'unit_price' => $product->price,
                    'quantity' => $item->quantity,
                    'line_total' => $product->price * $item->quantity,
                    'options' => $item->options,
                ]);

                if ($product->track_inventory) {
                    $product->decrement('stock_quantity', $item->quantity);
                }
            }

            return $order->load('items');
        });

        try {
            $providerData = $this->paymob->createIntention($order);
            $checkoutUrl = $this->paymob->checkoutUrl($providerData['client_secret']);

            $order->paymentIntentions()->create([
                'special_reference' => $order->order_number,
                'paymob_intention_id' => (string) $providerData['id'],
                'status' => 'pending',
                'amount_cents' => $order->grand_total,
                'currency' => $order->currency,
                'payment_methods' => config('services.paymob.integration_ids'),
                'is_live' => app()->environment('production'),
                'provider_data' => [...$providerData, 'checkout_url' => $checkoutUrl],
            ]);

            $user->cart?->items()->delete();
            AuditLog::create(['user_id' => $user->id, 'action' => 'checkout.created', 'auditable_type' => Order::class, 'auditable_id' => $order->id, 'ip_address' => request()->ip()]);

            return ['order' => $order, 'checkout_url' => $checkoutUrl];
        } catch (Throwable $exception) {
            DB::transaction(function () use ($order): void {
                foreach ($order->items()->with('product')->get() as $item) {
                    if ($item->product?->track_inventory) {
                        $item->product->increment('stock_quantity', $item->quantity);
                    }
                }
                $order->update(['status' => OrderStatus::Cancelled, 'payment_status' => PaymentStatus::Failed, 'cancelled_at' => now()]);
            });

            throw $exception;
        }
    }
}
