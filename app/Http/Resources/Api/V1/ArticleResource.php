<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;

class ArticleResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        $translation = $this->translate(app()->getLocale(), true);

        return [
            'id' => $this->id,
            'title' => $translation?->title,
            'slug' => $translation?->slug,
            'excerpt' => $translation?->excerpt,
            'content' => $this->when($request->routeIs('api.v1.articles.show'), $translation?->content),
            'meta_title' => $translation?->meta_title,
            'meta_description' => $translation?->meta_description,
            'is_featured' => $this->is_featured,
            'image_url' => $this->getFirstMediaUrl('article-images') ?: null,
            'published_at' => $this->published_at?->toAtomString(),
        ];
    }
}
