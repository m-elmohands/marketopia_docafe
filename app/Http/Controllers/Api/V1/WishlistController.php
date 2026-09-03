<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Wishlist\AddProductToWishlist;
use App\Actions\Wishlist\RemoveProductFromWishlist;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Wishlist\IndexWishlistRequest;
use App\Http\Requests\Api\V1\Wishlist\StoreWishlistItemRequest;
use App\Http\Resources\Api\V1\WishlistItemResource;
use App\Models\Product;
use App\Models\WishlistItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class WishlistController extends Controller
{
    public function index(IndexWishlistRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();
        $items = $request->user()->wishlistItems()
            ->with(['product.translations', 'product.categories.translations', 'product.media'])
            ->whereHas('product', fn ($query) => $query->available())
            ->when($validated['search'] ?? null, fn ($query, $search) => $query
                ->whereHas('product.translations', fn ($query) => $query->where('name', 'like', '%'.$search.'%')))
            ->orderBy('created_at', ($validated['sort'] ?? 'newest') === 'oldest' ? 'asc' : 'desc')
            ->paginate($validated['per_page'] ?? 15);

        return WishlistItemResource::collection($items);
    }

    public function store(StoreWishlistItemRequest $request, AddProductToWishlist $action): WishlistItemResource
    {
        $item = $action->handle($request->user(), $request->integer('product_id'));

        return new WishlistItemResource($item);
    }

    public function destroy(Request $request, Product $product, RemoveProductFromWishlist $action): JsonResponse
    {
        $item = WishlistItem::query()
            ->whereBelongsTo($request->user())
            ->whereBelongsTo($product)
            ->firstOrFail();

        Gate::authorize('delete', $item);
        $action->handle($item);

        return response()->json([
            'data' => null,
            'message' => __('Product removed from wishlist.'),
            'errors' => null,
        ]);
    }
}
