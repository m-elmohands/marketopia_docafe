<?php

namespace App\Actions\Wishlist;

use App\Models\WishlistItem;

class RemoveProductFromWishlist
{
    public function handle(WishlistItem $wishlistItem): void
    {
        $wishlistItem->delete();
    }
}
