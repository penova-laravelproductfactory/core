<?php

use App\Modules\Store\Controllers\CartAddController;
use App\Modules\Store\Controllers\CartRemoveController;
use App\Modules\Store\Controllers\CheckoutShowController;
use App\Modules\Store\Controllers\CheckoutStoreController;
use App\Modules\Store\Controllers\OrderConfirmationController;
use App\Modules\Store\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Modules\Store — PUBLIC routes (guest storefront + checkout)
|--------------------------------------------------------------------------
| Loaded by StoreServiceProvider::boot() under the plain "web" group —
| NO auth, NO admin prefix. URIs live at /store/... , names at
| store.front / store.cart.* / store.checkout.* — distinct from the
| admin surface (store.products.* / store.orders.* at /admin/store/...).
*/

Route::get('/store', StorefrontController::class)->name('store.front');

Route::post('/store/cart/add', CartAddController::class)->name('store.cart.add');
Route::post('/store/cart/remove', CartRemoveController::class)->name('store.cart.remove');

Route::get('/store/checkout', CheckoutShowController::class)->name('store.checkout.show');
Route::post('/store/checkout', CheckoutStoreController::class)->name('store.checkout.store');

Route::get('/store/orders/{number}/confirmation', OrderConfirmationController::class)->name('store.checkout.confirmation');
