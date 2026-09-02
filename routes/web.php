<?php

use App\Http\Controllers\PaymentResultController;
use App\Http\Middleware\SetFilamentLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/payment/result', PaymentResultController::class)->name('payment.result');

Route::view('documentation', 'api.documentation')->name('api.documentation');
Route::get('openapi.yaml', function () {
    return response()->file(base_path('docs/api/openapi.yaml'), [
        'Content-Type' => 'application/yaml; charset=UTF-8',
        'Content-Disposition' => 'inline; filename="openapi.yaml"',
    ]);
})->name('api.openapi');
Route::get('postman-collection', function () {
    return response()->file(
        base_path('docs/api/do-cafe.postman_collection.json'),
        [
            'Content-Type' => 'application/json; charset=UTF-8',
            'Content-Disposition' => 'inline',
        ],
    );
})->name('api.postman');

Route::get('/admin/locale/{locale}', function (string $locale): RedirectResponse {
    abort_unless(in_array($locale, SetFilamentLocale::SUPPORTED_LOCALES, true), 404);

    session(['filament_locale' => $locale]);

    return redirect()->back();
})->middleware('auth')->name('admin.locale');
