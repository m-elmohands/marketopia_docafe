<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;

class ProductStageResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        $translation = $this->translate(app()->getLocale(), true);

        return [
            'id' => $this->id,
            'title' => $translation?->title,
            'description' => $translation?->description,
            'sort_order' => $this->sort_order,
            'image_url' => $this->getFirstMediaUrl('stage-image') ?: null,
        ];
    }
}
