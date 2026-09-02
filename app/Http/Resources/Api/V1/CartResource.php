<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;

class CartResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        $items = $this->items;

        return [
            'id' => $this->id,
            'currency' => $this->currency->value,
            'items' => $items->map(fn ($item): array => [
                'id' => $item->id,
                'product' => new ProductResource($item->product),
                'quantity' => $item->quantity,
                'unit_price' => $item->product->price,
                'line_total' => $item->product->price * $item->quantity,
                'options' => $item->options,
            ]),
            'items_count' => $items->sum('quantity'),
            'subtotal' => $items->sum(fn ($item): int => $item->product->price * $item->quantity),
        ];
    }
}
