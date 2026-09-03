<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;

class OrderResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'idempotency_key' => $this->idempotency_key,
            'order_type' => $this->order_type->value,
            'status' => $this->status->value,
            'payment_status' => $this->payment_status->value,
            'payment_method' => $this->payment_method?->value,
            'fulfillment_status' => $this->fulfillment_status->value,
            'currency' => $this->currency->value,
            'subtotal' => $this->subtotal,
            'discount_total' => $this->discount_total,
            'shipping_total' => $this->shipping_total,
            'tax_total' => $this->tax_total,
            'grand_total' => $this->grand_total,
            'shipping_address' => $this->shipping_address,
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item): array => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $item->product_name,
                'sku' => $item->sku,
                'unit_price' => $item->unit_price,
                'quantity' => $item->quantity,
                'line_total' => $item->line_total,
                'options' => $item->options,
            ])),
            'placed_at' => $this->placed_at?->toAtomString(),
            'paid_at' => $this->paid_at?->toAtomString(),
        ];
    }
}
