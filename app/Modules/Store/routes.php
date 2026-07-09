<?php

use App\Modules\Store\Controllers\ActiveProductsCountController;
use App\Modules\Store\Controllers\OrderIndexController;
use App\Modules\Store\Controllers\OrderShowController;
use App\Modules\Store\Controllers\OrderUpdateController;
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
| file inside the Workspace group (URI prefix + auth middleware from
| config('penova.workspace')), so URIs land under the configured Workspace
| prefix, e.g. /workspace/store/products/... .
| Route names carry the module's own "store." prefix explicitly.
|
| Permissions (seeded by StorePermissionsSeeder):
|   store.view   → products list + the widget's active-count JSON
|   store.manage → create / store / edit / update / delete
*/

Route::middleware('permission:store.view')->group(function () {
    // Widget data endpoint. Registered before the {product}
    // parameterised routes so "active-count" is never captured as a
    // model key.
    Route::get('/store/products/active-count', ActiveProductsCountController::class)->name('store.products.active-count');

    Route::get('/store/products', ProductIndexController::class)->name('store.products.index');

    Route::get('/store/orders', OrderIndexController::class)->name('store.orders.index');
    Route::get('/store/orders/{order}', OrderShowController::class)->name('store.orders.show');
});

Route::middleware('permission:store.manage')->group(function () {
    Route::get('/store/products/create', ProductCreateController::class)->name('store.products.create');
    Route::post('/store/products', ProductStoreController::class)->name('store.products.store');
    Route::get('/store/products/{product}/edit', ProductEditController::class)->name('store.products.edit');
    Route::match(['put', 'patch'], '/store/products/{product}', ProductUpdateController::class)->name('store.products.update');
    Route::delete('/store/products/{product}', ProductDeleteController::class)->name('store.products.destroy');

    // Lifecycle-only order edits (status / payment_status).
    Route::match(['put', 'patch'], '/store/orders/{order}', OrderUpdateController::class)->name('store.orders.update');
});
