<?php

use App\Modules\Store\Controllers\ActiveProductsCountController;
use App\Modules\Store\Controllers\ProductCreateController;
use App\Modules\Store\Controllers\ProductDeleteController;
use App\Modules\Store\Controllers\ProductEditController;
use App\Modules\Store\Controllers\ProductIndexController;
use App\Modules\Store\Controllers\ProductStoreController;
use App\Modules\Store\Controllers\ProductUpdateController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Modules\Store Routes
|--------------------------------------------------------------------------
| Plain route definitions only — StoreServiceProvider::boot() loads this
| file inside the panel group (admin URI prefix + auth middleware from
| config('penova.admin')), so URIs land at /admin/store/products/... .
| Route names carry the module's own "store." prefix explicitly.
|
| Permissions (seeded by StorePermissionsSeeder):
|   store.view   → products list + the widget's active-count JSON
|   store.manage → create / store / edit / update / delete
*/

Route::middleware('permission:store.view')->group(function () {
    // Dashboard-widget data endpoint. Registered before the {product}
    // parameterised routes so "active-count" is never captured as a
    // model key.
    Route::get('/store/products/active-count', ActiveProductsCountController::class)->name('store.products.active-count');

    Route::get('/store/products', ProductIndexController::class)->name('store.products.index');
});

Route::middleware('permission:store.manage')->group(function () {
    Route::get('/store/products/create', ProductCreateController::class)->name('store.products.create');
    Route::post('/store/products', ProductStoreController::class)->name('store.products.store');
    Route::get('/store/products/{product}/edit', ProductEditController::class)->name('store.products.edit');
    Route::match(['put', 'patch'], '/store/products/{product}', ProductUpdateController::class)->name('store.products.update');
    Route::delete('/store/products/{product}', ProductDeleteController::class)->name('store.products.destroy');
});
