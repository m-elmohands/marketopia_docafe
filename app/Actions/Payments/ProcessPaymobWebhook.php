<?php

namespace App\Actions\Payments;

use App\Enums\OrderStatus;
use App\Enums\PaymentProcessingStatus;
use App\Enums\PaymentStatus;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\PaymentIntention;
use App\Models\PaymentTransaction;
use App\Models\PaymentWebhook;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProcessPaymobWebhook
{
    private const HMAC_FIELDS = [
        'amount_cents', 'created_at', 'currency', 'error_occured', 'has_parent_transaction',
        'id', 'integration_id', 'is_3d_secure', 'is_auth', 'is_capture', 'is_refunded',
        'is_standalone_payment', 'is_voided', 'order.id', 'owner', 'pending',
        'source_data.pan', 'source_data.sub_type', 'source_data.type', 'success',
    ];

    public function execute(array $payload, ?string $hmac): PaymentWebhook
    {
        $object = is_array($payload['obj'] ?? null) ? $payload['obj'] : $payload;
        $verified = $this->verifyHmac($object, $hmac);
        $fingerprint = hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR));

        $webhook = PaymentWebhook::firstOrCreate(['fingerprint' => $fingerprint], [
            'event_type' => (string) ($payload['type'] ?? 'TRANSACTION'),
            'paymob_transaction_id' => isset($object['id']) ? (string) $object['id'] : null,
            'hmac_verified' => $verified,
            'status' => $verified ? 'pending' : 'failed',
            'payload' => $payload,
            'processing_error' => $verified ? null : 'Invalid HMAC signature.',
            'received_at' => now(),
        ]);

        if ($verified && ! $webhook->hmac_verified) {
            $webhook->update(['hmac_verified' => true, 'status' => 'pending', 'processing_error' => null]);
        }

        if (! $verified || $webhook->processed_at) {
            return $webhook;
        }

        return DB::transaction(function () use ($webhook, $object): PaymentWebhook {
            $reference = (string) (Arr::get($object, 'order.merchant_order_id') ?: Arr::get($object, 'order.id'));
            $intention = PaymentIntention::query()
                ->where('special_reference', $reference)
                ->orWhere('paymob_intention_id', (string) Arr::get($object, 'intention.id', ''))
                ->first();

            if (! $intention) {
                $webhook->update(['status' => 'failed', 'processing_error' => 'Payment intention was not found.', 'processing_attempts' => 1]);

                return $webhook;
            }

            $success = filter_var($object['success'] ?? false, FILTER_VALIDATE_BOOL);
            $pending = filter_var($object['pending'] ?? false, FILTER_VALIDATE_BOOL);
            $transaction = PaymentTransaction::updateOrCreate(
                ['paymob_transaction_id' => (string) $object['id']],
                [
                    'payment_intention_id' => $intention->id,
                    'paymob_order_id' => (string) Arr::get($object, 'order.id', ''),
                    'integration_id' => (string) ($object['integration_id'] ?? ''),
                    'status' => $pending ? PaymentProcessingStatus::Pending : ($success ? PaymentProcessingStatus::Succeeded : PaymentProcessingStatus::Failed),
                    'amount_cents' => (int) ($object['amount_cents'] ?? $intention->amount_cents),
                    'currency' => strtoupper((string) ($object['currency'] ?? $intention->currency->value)),
                    'source_type' => Arr::get($object, 'source_data.type'),
                    'source_subtype' => Arr::get($object, 'source_data.sub_type'),
                    'masked_pan' => Arr::get($object, 'source_data.pan'),
                    'is_success' => $success,
                    'is_pending' => $pending,
                    'is_authorized' => filter_var($object['is_auth'] ?? false, FILTER_VALIDATE_BOOL),
                    'is_captured' => filter_var($object['is_capture'] ?? false, FILTER_VALIDATE_BOOL),
                    'is_voided' => filter_var($object['is_voided'] ?? false, FILTER_VALIDATE_BOOL),
                    'is_refunded' => filter_var($object['is_refunded'] ?? false, FILTER_VALIDATE_BOOL),
                    'is_3d_secure' => filter_var($object['is_3d_secure'] ?? false, FILTER_VALIDATE_BOOL),
                    'error_message' => $object['data.message'] ?? null,
                    'provider_data' => $object,
                    'processed_at' => now(),
                ],
            );

            $intention->update([
                'status' => $pending ? PaymentProcessingStatus::Pending : ($success ? PaymentProcessingStatus::Succeeded : PaymentProcessingStatus::Failed),
                'paid_at' => $success ? now() : null,
                'failed_at' => ! $success && ! $pending ? now() : null,
            ]);

            $order = $intention->order;
            if ($success && (int) $object['amount_cents'] === $order->grand_total && strtoupper((string) $object['currency']) === $order->currency->value) {
                $order->update(['payment_status' => PaymentStatus::Paid, 'status' => OrderStatus::Processing, 'paid_at' => now(), 'payment_reference' => (string) $object['id']]);
            } elseif (! $pending) {
                $order->update(['payment_status' => PaymentStatus::Failed]);
            }

            $webhook->update(['payment_transaction_id' => $transaction->id, 'status' => 'processed', 'processing_attempts' => 1, 'processed_at' => now()]);
            AuditLog::create(['action' => 'payment.webhook_processed', 'auditable_type' => Order::class, 'auditable_id' => $order->id, 'context' => ['transaction_id' => $transaction->id]]);

            return $webhook;
        });
    }

    public function verifyHmac(array $object, ?string $hmac): bool
    {
        if (blank($hmac) || blank(config('services.paymob.hmac_secret'))) {
            return false;
        }

        $data = collect(self::HMAC_FIELDS)->map(fn (string $field): string => $this->stringify(Arr::get($object, $field)))->implode('');

        return hash_equals(hash_hmac('sha512', $data, (string) config('services.paymob.hmac_secret')), Str::lower($hmac));
    }

    private function stringify(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return (string) $value;
    }
}
