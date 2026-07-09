<?php

use App\Modules\Store\Controllers\AccountOrderController;
use App\Modules\Store\Controllers\CartAddController;
use App\Modules\Store\Controllers\CartRemoveController;
use App\Modules\Store\Controllers\CheckoutShowController;
use App\Modules\Store\Controllers\CheckoutStoreController;
use App\Modules\Store\Controllers\OrderConfirmationController;
use App\Modules\Store\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Modules\Store — PUBLIC routes (storefront + checkout)
|--------------------------------------------------------------------------
| Loaded by StoreServiceProvider::boot() under the plain "web" group —
| NO Workspace prefix. URIs live at /store/... , names at store.front /
| store.cart.* / store.checkout.* — distinct from the Workspace surface
| (store.products.* / store.orders.* under the Workspace prefix).
|
| Browsing and the cart stay guest-friendly (zero friction); checkout
| and order pages require an account. A guest hitting checkout is sent
| to /login (redirectGuestsTo) and Laravel's intended-URL mechanism
| brings them straight back after login/registration.
*/

Route::get('/store', StorefrontController::class)->name('store.front');

Route::post('/store/cart/add', CartAddController::class)->name('store.cart.add');
Route::post('/store/cart/remove', CartRemoveController::class)->name('store.cart.remove');

Route::middleware('auth')->group(function () {
    Route::get('/store/checkout', CheckoutShowController::class)->name('store.checkout.show');
    Route::post('/store/checkout', CheckoutStoreController::class)->name('store.checkout.store');

    // Owner-only: the confirmation page checks order->user_id.
    Route::get('/store/orders/{number}/confirmation', OrderConfirmationController::class)->name('store.checkout.confirmation');

    // Customer order history — owner-scoped in the controller (index
    // filters by user_id; show 404s for a non-owner).
    Route::get('/store/account/orders', [AccountOrderController::class, 'index'])->name('store.account.orders.index');
    Route::get('/store/account/orders/{number}', [AccountOrderController::class, 'show'])->name('store.account.orders.show');
});
