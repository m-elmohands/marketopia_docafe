<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;

class WishlistItemResource extends ApiResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product' => new ProductResource($this->whenLoaded('product')),
            'added_at' => $this->created_at,
        ];
    }
}
