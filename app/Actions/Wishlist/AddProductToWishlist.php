<?php

namespace App\Actions\Wishlist;

use App\Models\Product;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AddProductToWishlist
{
    public function handle(User $user, int $productId): WishlistItem
    {
        $product = Product::query()->available()->find($productId);

        if ($product === null) {
            throw ValidationException::withMessages([
                'product_id' => [__('The selected product is not available.')],
            ]);
        }

        return DB::transaction(function () use ($user, $product): WishlistItem {
            $item = WishlistItem::withTrashed()->firstOrNew([
                'user_id' => $user->id,
                'product_id' => $product->id,
            ]);

            if ($item->trashed()) {
                $item->restore();
            } elseif (! $item->exists) {
                $item->save();
            }

            return $item->load('product.translations', 'product.categories.translations', 'product.media');
        });
    }
}
