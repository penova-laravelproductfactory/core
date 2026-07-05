<?php

/*
|--------------------------------------------------------------------------
| Store — the order-flow contract (v0.1 guest checkout)
|--------------------------------------------------------------------------
| A guest browses the storefront, fills a session cart, places an order
| through the one-page checkout; the order snapshots names/prices and
| totals are computed server-side. The admin then finds it in the panel
| and walks it through the lifecycle (confirm + mark paid).
*/

use App\Modules\Store\Models\Order;
use App\Modules\Store\Models\Product;
use Database\Seeders\PenovaCoreSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('guest checkout creates an order and the admin manages its lifecycle', function () {
    $this->seed(PenovaCoreSeeder::class);
    $this->seed(\App\Modules\Store\Database\Seeders\StorePermissionsSeeder::class);

    $product = Product::create([
        'name' => 'محصول تستی',
        'slug' => 'test-product',
        'type' => 'physical',
        'price' => 150.50,
        'stock' => 10,
    ]);

    // Inactive products must never be sellable.
    $inactive = Product::create([
        'name' => 'غیرفعال',
        'slug' => 'inactive-product',
        'type' => 'physical',
        'price' => 99,
        'is_active' => false,
    ]);

    // 1) Storefront renders publicly (guest, no auth).
    $this->get('/store')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Modules/Store/Storefront/Index')
            ->has('products.data', 1)); // only the active product

    // 2) Add the product twice → one line, quantity 2.
    $this->post('/store/cart/add', ['product_id' => $product->id])->assertRedirect();
    $this->post('/store/cart/add', ['product_id' => $product->id])->assertRedirect();

    // Inactive products are rejected at validation.
    $this->post('/store/cart/add', ['product_id' => $inactive->id])->assertSessionHasErrors('product_id');

    // 3) Checkout page shows the resolved cart.
    $this->get('/store/checkout')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Modules/Store/Checkout/Index')
            ->has('lines', 1)
            ->where('lines.0.quantity', 2)
            // 301.0 serialises to the JSON integer 301.
            ->where('total', 301));

    // 4) Place the order.
    $this->post('/store/checkout', [
        'customer_name' => 'مشتری تستی',
        'customer_email' => 'customer@example.com',
        'customer_phone' => '09120000000',
        'shipping_address' => 'تهران، خیابان تست، پلاک ۱',
        'notes' => 'لطفاً صبح ارسال شود.',
        // Client-sent totals must be ignored (server computes them).
        'total' => 1,
    ])->assertRedirect();

    $order = Order::with('items')->firstOrFail();

    expect($order->status)->toBe('pending')
        ->and($order->payment_status)->toBe('unpaid')
        ->and((float) $order->total)->toBe(301.0)
        ->and($order->number)->toStartWith('ORD-')
        ->and($order->items)->toHaveCount(1)
        ->and($order->items[0]->product_name)->toBe('محصول تستی') // snapshot
        ->and((float) $order->items[0]->price)->toBe(150.5)
        ->and($order->items[0]->quantity)->toBe(2);

    // 5) Confirmation page is reachable by order number; cart is empty
    //    again so checkout bounces back to the storefront.
    $this->get("/store/orders/{$order->number}/confirmation")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Modules/Store/Checkout/Confirmation')
            ->where('order.number', $order->number));

    $this->get('/store/checkout')->assertRedirect(route('store.front'));

    // 6) Admin side: orders are permission-guarded, listed, and the
    //    lifecycle actions work.
    $this->get('/admin/store/orders')->assertRedirect(route('login')); // guest

    $this->post('/login', [
        'email' => config('penova.admin.email'),
        'password' => config('penova.admin.password'),
    ]);

    $this->get('/admin/store/orders')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Modules/Store/Orders/Index')
            ->where('orders.data.0.number', $order->number));

    $this->get("/admin/store/orders/{$order->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Modules/Store/Orders/Show')
            ->where('order.total', $order->total));

    $this->put("/admin/store/orders/{$order->id}", ['status' => 'confirmed'])->assertRedirect();
    $this->put("/admin/store/orders/{$order->id}", ['payment_status' => 'paid'])->assertRedirect();

    $order->refresh();
    expect($order->status)->toBe('confirmed')
        ->and($order->payment_status)->toBe('paid');

    // Lifecycle-only contract: unknown statuses are rejected.
    $this->put("/admin/store/orders/{$order->id}", ['status' => 'shipped'])->assertSessionHasErrors('status');
});
