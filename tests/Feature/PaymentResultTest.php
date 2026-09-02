<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Tests\TestCase;

class PaymentResultTest extends TestCase
{
    use RefreshDatabase;

    public function test_signed_paymob_redirect_marks_order_paid_and_displays_success(): void
    {
        config()->set('services.paymob.hmac_secret', 'hmac-secret');
        $order = $this->orderWithIntention();
        $object = $this->paymobObject($order);
        $query = $this->redirectQuery($object);
        $query['hmac'] = $this->hmac($object);

        $this->get(route('payment.result', $query))
            ->assertOk()
            ->assertSee('Payment completed successfully')
            ->assertSee($order->order_number);

        $this->assertSame(PaymentStatus::Paid, $order->fresh()->payment_status);
        $this->assertDatabaseHas('payment_transactions', [
            'paymob_transaction_id' => '527213962',
            'is_success' => true,
        ]);
    }

    public function test_paymob_redirect_with_invalid_signature_is_rejected(): void
    {
        config()->set('services.paymob.hmac_secret', 'hmac-secret');
        $order = $this->orderWithIntention();
        $query = $this->redirectQuery($this->paymobObject($order));
        $query['hmac'] = 'invalid';

        $this->get(route('payment.result', $query))
            ->assertBadRequest()
            ->assertSee('Invalid payment response');

        $this->assertSame(PaymentStatus::Pending, $order->fresh()->payment_status);
        $this->assertDatabaseCount('payment_transactions', 0);
    }

    private function orderWithIntention(): Order
    {
        $user = User::factory()->create();
        $order = Order::create([
            'order_number' => 'DC-TEST-REDIRECT',
            'user_id' => $user->id,
            'status' => 'pending',
            'payment_status' => 'pending',
            'fulfillment_status' => 'unfulfilled',
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'shipping_address' => ['city' => 'Cairo'],
            'currency' => 'EGP',
            'subtotal' => 24_000,
            'grand_total' => 24_000,
            'placed_at' => now(),
        ]);
        $order->paymentIntentions()->create([
            'special_reference' => $order->order_number,
            'paymob_intention_id' => 'intent-redirect',
            'status' => 'pending',
            'amount_cents' => 24_000,
            'currency' => 'EGP',
        ]);

        return $order;
    }

    private function paymobObject(Order $order): array
    {
        return [
            'amount_cents' => '24000', 'created_at' => '2026-09-02T17:53:35.604512', 'currency' => 'EGP',
            'error_occured' => 'false', 'has_parent_transaction' => 'false', 'id' => '527213962',
            'integration_id' => '4555948', 'is_3d_secure' => 'true', 'is_auth' => 'false',
            'is_capture' => 'false', 'is_refunded' => 'false', 'is_standalone_payment' => 'true',
            'is_voided' => 'false', 'order' => ['id' => '600637758', 'merchant_order_id' => $order->order_number],
            'owner' => '1777791', 'pending' => 'false',
            'source_data' => ['pan' => '0007', 'sub_type' => 'MasterCard', 'type' => 'card'],
            'success' => 'true', 'data' => ['message' => 'Approved'],
        ];
    }

    private function redirectQuery(array $object): array
    {
        return [
            'amount_cents' => $object['amount_cents'], 'created_at' => $object['created_at'],
            'currency' => $object['currency'], 'error_occured' => $object['error_occured'],
            'has_parent_transaction' => $object['has_parent_transaction'], 'id' => $object['id'],
            'integration_id' => $object['integration_id'], 'is_3d_secure' => $object['is_3d_secure'],
            'is_auth' => $object['is_auth'], 'is_capture' => $object['is_capture'],
            'is_refunded' => $object['is_refunded'], 'is_standalone_payment' => $object['is_standalone_payment'],
            'is_voided' => $object['is_voided'], 'order' => $object['order']['id'],
            'merchant_order_id' => $object['order']['merchant_order_id'], 'owner' => $object['owner'],
            'pending' => $object['pending'], 'source_data_pan' => $object['source_data']['pan'],
            'source_data_sub_type' => $object['source_data']['sub_type'],
            'source_data_type' => $object['source_data']['type'], 'success' => $object['success'],
            'data_message' => $object['data']['message'],
        ];
    }

    private function hmac(array $object): string
    {
        $fields = [
            'amount_cents', 'created_at', 'currency', 'error_occured', 'has_parent_transaction',
            'id', 'integration_id', 'is_3d_secure', 'is_auth', 'is_capture', 'is_refunded',
            'is_standalone_payment', 'is_voided', 'order.id', 'owner', 'pending',
            'source_data.pan', 'source_data.sub_type', 'source_data.type', 'success',
        ];
        $data = collect($fields)->map(fn (string $field): string => (string) Arr::get($object, $field))->implode('');

        return hash_hmac('sha512', $data, 'hmac-secret');
    }
}
