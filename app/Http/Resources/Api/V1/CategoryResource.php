<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;

class CategoryResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        $translation = $this->translate(app()->getLocale(), true);

        return [
            'id' => $this->id,
            'name' => $translation?->name,
            'slug' => $translation?->slug,
            'description' => $translation?->description,
            'is_featured' => $this->is_featured,
            'image_url' => $this->getFirstMediaUrl('category-images') ?: null,
            'children' => self::collection($this->whenLoaded('children')),
        ];
    }
}
