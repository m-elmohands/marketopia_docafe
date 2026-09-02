<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\CatalogStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $categories = Category::query()
            ->with(['translations', 'media', 'children.translations', 'children.media'])
            ->whereNull('parent_id')
            ->where('status', CatalogStatus::Active)
            ->orderBy('sort_order')
            ->get();

        return CategoryResource::collection($categories);
    }
}
