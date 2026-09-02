<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PaymentIntention;
use App\Models\PaymentRefund;
use App\Models\PaymentTransaction;
use App\Models\PaymentWebhook;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymobPaymentModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_paymob_payment_records_preserve_the_checkout_lifecycle(): void
    {
        $user = User::factory()->create();
        $order = Order::create([
            'order_number' => 'ORD-1001',
            'user_id' => $user->id,
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'shipping_address' => ['country' => 'EG', 'city' => 'Cairo'],
            'currency' => 'EGP',
            'subtotal' => 10_000,
            'grand_total' => 10_000,
        ]);

        $intention = PaymentIntention::create([
            'order_id' => $order->id,
            'special_reference' => 'ORD-1001-PAY-1',
            'paymob_intention_id' => 'pi_test_123',
            'amount_cents' => 10_000,
            'currency' => 'EGP',
            'payment_methods' => [123456],
        ]);

        $transaction = PaymentTransaction::create([
            'payment_intention_id' => $intention->id,
            'paymob_transaction_id' => '477136036',
            'paymob_order_id' => '544056382',
            'status' => 'succeeded',
            'amount_cents' => 10_000,
            'currency' => 'EGP',
            'source_type' => 'card',
            'masked_pan' => 'xxxx-xxxx-xxxx-2346',
            'is_success' => true,
            'is_pending' => false,
            'is_3d_secure' => true,
        ]);

        $refund = PaymentRefund::create([
            'payment_transaction_id' => $transaction->id,
            'requested_by' => $user->id,
            'status' => 'pending',
            'amount_cents' => 2_500,
            'currency' => 'EGP',
        ]);

        $webhook = PaymentWebhook::create([
            'payment_transaction_id' => $transaction->id,
            'fingerprint' => hash('sha256', 'TRANSACTION:477136036:succeeded'),
            'event_type' => 'TRANSACTION',
            'paymob_transaction_id' => '477136036',
            'hmac_verified' => true,
            'payload' => ['obj' => ['id' => 477136036, 'success' => true]],
            'received_at' => now(),
        ]);

        $this->assertTrue($order->paymentIntentions->contains($intention));
        $this->assertTrue($intention->transactions->contains($transaction));
        $this->assertTrue($transaction->refunds->contains($refund));
        $this->assertTrue($transaction->webhooks->contains($webhook));
        $this->assertTrue($transaction->is_success);
        $this->assertTrue($webhook->hmac_verified);
        $this->assertSame(477136036, $webhook->payload['obj']['id']);
    }
}
