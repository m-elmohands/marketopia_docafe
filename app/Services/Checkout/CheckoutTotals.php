<?php

namespace App\Services\Checkout;

use App\Models\Setting;

class CheckoutTotals
{
    /** @return array{subtotal: int, shipping_total: int, tax_total: int, grand_total: int} */
    public function calculate(int $subtotal): array
    {
        $settings = Setting::query()
            ->with('translations')
            ->whereIn('key', ['delivery_fee', 'tax_percentage'])
            ->get()
            ->mapWithKeys(fn (Setting $setting): array => [$setting->key => $setting->translate('en')?->value]);

        $shipping = max(0, (int) $settings->get('delivery_fee', 0));
        $taxPercentage = max(0, (float) $settings->get('tax_percentage', 0));
        $tax = (int) round($subtotal * $taxPercentage / 100);

        return [
            'subtotal' => $subtotal,
            'shipping_total' => $shipping,
            'tax_total' => $tax,
            'grand_total' => $subtotal + $shipping + $tax,
        ];
    }
}
