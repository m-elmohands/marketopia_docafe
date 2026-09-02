<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\CatalogStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProductResource;
use App\Models\Product;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $products = Product::query()
            ->with(['translations', 'categories.translations', 'media'])
            ->where('status', CatalogStatus::Active)
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->when(request('category'), fn ($query, $slug) => $query->whereHas('categories.translations', fn ($query) => $query->where('slug', $slug)))
            ->when(request('featured'), fn ($query, $featured) => $query->where('is_featured', filter_var($featured, FILTER_VALIDATE_BOOL)))
            ->when(request('search'), fn ($query, $search) => $query->whereHas('translations', fn ($query) => $query->where('name', 'like', '%'.$search.'%')))
            ->when(request('min_price'), fn ($query, $price) => $query->where('price', '>=', (int) $price))
            ->when(request('max_price'), fn ($query, $price) => $query->where('price', '<=', (int) $price))
            ->orderBy(match (request('sort')) {
                'price' => 'price',
                '-price' => 'price',
                default => 'published_at',
            }, request('sort') === 'price' ? 'asc' : 'desc')
            ->paginate(min(max((int) request('per_page', 15), 1), 50));

        return ProductResource::collection($products);
    }

    public function show(string $slug): ProductResource
    {
        $product = Product::query()
            ->with(['translations', 'categories.translations', 'stages.translations', 'stages.media', 'media'])
            ->where('status', CatalogStatus::Active)
            ->whereHas('translations', fn ($query) => $query->where('slug', $slug))
            ->firstOrFail();

        return new ProductResource($product);
    }
}
