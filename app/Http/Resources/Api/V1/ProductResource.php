<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;

class ProductResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        $translation = $this->translate(app()->getLocale(), true);

        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $translation?->name,
            'slug' => $translation?->slug,
            'short_description' => $translation?->short_description,
            'description' => $translation?->description,
            'price' => $this->price,
            'compare_at_price' => $this->compare_at_price,
            'currency' => $this->currency->value,
            'in_stock' => ! $this->track_inventory || $this->stock_quantity > 0,
            'stock_quantity' => $this->when($request->user()?->can('products.viewAny'), $this->stock_quantity),
            'is_featured' => $this->is_featured,
            'image_url' => $this->getFirstMediaUrl('product-images') ?: null,
            'images' => $this->whenLoaded('media', fn () => $this->getMedia('product-images')->map->getUrl()->values()),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'stages' => ProductStageResource::collection($this->whenLoaded('stages')),
        ];
    }
}
