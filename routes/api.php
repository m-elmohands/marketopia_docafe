<?php

use App\Http\Controllers\Api\PostmanCollectionController;
use App\Http\Controllers\Api\V1\ArticleController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PaymobWebhookController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\SettingController;
use Illuminate\Support\Facades\Route;

Route::view('documentation', 'api.documentation')->name('api.documentation');
Route::get('openapi.yaml', function () {
    return response()->file(base_path('docs/api/openapi.yaml'), [
        'Content-Type' => 'application/yaml; charset=UTF-8',
        'Content-Disposition' => 'inline; filename="openapi.yaml"',
    ]);
})->name('api.openapi');
Route::get('postman-collection', PostmanCollectionController::class)->name('api.postman');

Route::prefix('v1')->name('api.v1.')->middleware(['api.locale', 'throttle:api'])->group(function (): void {
    Route::post('auth/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('auth/login', [AuthController::class, 'login'])->name('auth.login');

    Route::get('products', [ProductController::class, 'index'])->name('products.index');
    Route::get('products/{slug}', [ProductController::class, 'show'])->name('products.show');
    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('articles', [ArticleController::class, 'index'])->name('articles.index');
    Route::get('articles/{slug}', [ArticleController::class, 'show'])->name('articles.show');
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('payments/paymob/webhook', PaymobWebhookController::class)->name('payments.paymob.webhook');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('auth/user', [AuthController::class, 'user'])->name('auth.user');
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('cart', [CartController::class, 'show'])->name('cart.show');
        Route::post('cart/items', [CartController::class, 'store'])->name('cart.items.store');
        Route::patch('cart/items/{cartItem}', [CartController::class, 'update'])->name('cart.items.update');
        Route::delete('cart/items/{cartItem}', [CartController::class, 'destroy'])->name('cart.items.destroy');
        Route::post('checkout', [CheckoutController::class, 'store'])->name('checkout.store');
        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    });
});
