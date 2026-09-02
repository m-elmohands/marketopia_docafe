<?php

namespace App\Services\Payments;

use App\Models\Order;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PaymobClient
{
    public function createIntention(Order $order): array
    {
        $response = $this->request()->post('/v1/intention/', [
            'amount' => $order->grand_total,
            'currency' => $order->currency->value,
            'payment_methods' => array_map('intval', config('services.paymob.integration_ids')),
            'items' => $order->items->map(fn ($item): array => [
                'name' => $item->product_name,
                'amount' => $item->unit_price,
                'description' => $item->sku,
                'quantity' => $item->quantity,
            ])->all(),
            'billing_data' => $this->billingData($order),
            'special_reference' => $order->order_number,
            'notification_url' => config('services.paymob.notification_url'),
            'redirection_url' => config('services.paymob.redirection_url'),
        ])->throw()->json();

        if (! is_array($response) || empty($response['id']) || empty($response['client_secret'])) {
            throw new RuntimeException('Paymob returned an invalid payment intention response.');
        }

        return $response;
    }

    public function checkoutUrl(string $clientSecret): string
    {
        return rtrim((string) config('services.paymob.base_url'), '/').'/unifiedcheckout/?'.http_build_query([
            'publicKey' => config('services.paymob.public_key'),
            'clientSecret' => $clientSecret,
        ]);
    }

    private function request(): PendingRequest
    {
        if (blank(config('services.paymob.secret_key')) || blank(config('services.paymob.public_key')) || config('services.paymob.integration_ids') === []) {
            throw new RuntimeException('Paymob credentials are not configured.');
        }

        return Http::baseUrl(config('services.paymob.base_url'))
            ->withToken(config('services.paymob.secret_key'))
            ->acceptJson()
            ->asJson()
            ->timeout(config('services.paymob.timeout'))
            ->retry(2, 250);
    }

    private function billingData(Order $order): array
    {
        $address = $order->billing_address ?: $order->shipping_address;
        $parts = preg_split('/\s+/', trim($order->customer_name), 2);

        return [
            'first_name' => $parts[0] ?: 'Customer',
            'last_name' => $parts[1] ?? $parts[0] ?? 'Customer',
            'email' => $order->customer_email,
            'phone_number' => $order->customer_phone,
            'apartment' => $address['apartment'] ?? 'NA',
            'building' => $address['building'] ?? 'NA',
            'street' => $address['street'] ?? 'NA',
            'city' => $address['city'] ?? 'NA',
            'state' => $address['state'] ?? 'NA',
            'country' => strtoupper($address['country'] ?? 'EG'),
            'postal_code' => $address['postal_code'] ?? 'NA',
            'floor' => $address['floor'] ?? 'NA',
        ];
    }
}
