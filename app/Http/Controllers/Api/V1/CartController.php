<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\CatalogStatus;
use App\Enums\Currency;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Cart\StoreCartItemRequest;
use App\Http\Requests\Api\V1\Cart\UpdateCartItemRequest;
use App\Http\Resources\Api\V1\CartResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function show(Request $request): CartResource
    {
        return new CartResource($this->cart($request)->load('items.product.translations'));
    }

    public function store(StoreCartItemRequest $request): CartResource
    {
        $product = Product::query()->where('status', CatalogStatus::Active)->findOrFail($request->integer('product_id'));
        $quantity = $request->integer('quantity');

        if ($product->track_inventory && $product->stock_quantity < $quantity) {
            throw ValidationException::withMessages(['quantity' => [__('The requested quantity is not available.')]]);
        }

        $cart = $this->cart($request);
        $item = $cart->items()->firstOrNew(['product_id' => $product->id]);
        $item->quantity = $item->exists ? $item->quantity + $quantity : $quantity;
        $item->options = $request->input('options');

        if ($product->track_inventory && $product->stock_quantity < $item->quantity) {
            throw ValidationException::withMessages(['quantity' => [__('The requested quantity is not available.')]]);
        }

        $item->save();

        return new CartResource($cart->load('items.product.translations'));
    }

    public function update(UpdateCartItemRequest $request, CartItem $cartItem): CartResource
    {
        $cart = $this->cart($request);
        abort_unless($cartItem->cart_id === $cart->id, 404);
        $cartItem->load('product');

        if ($cartItem->product->track_inventory && $cartItem->product->stock_quantity < $request->integer('quantity')) {
            throw ValidationException::withMessages(['quantity' => [__('The requested quantity is not available.')]]);
        }

        $cartItem->update(['quantity' => $request->integer('quantity')]);

        return new CartResource($cart->load('items.product.translations'));
    }

    public function destroy(Request $request, CartItem $cartItem): CartResource
    {
        $cart = $this->cart($request);
        abort_unless($cartItem->cart_id === $cart->id, 404);
        $cartItem->delete();

        return new CartResource($cart->load('items.product.translations'));
    }

    private function cart(Request $request): Cart
    {
        return Cart::firstOrCreate(['user_id' => $request->user()->id], ['currency' => Currency::EGP]);
    }
}
