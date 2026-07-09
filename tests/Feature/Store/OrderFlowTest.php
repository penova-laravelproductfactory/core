<?php

/*
|--------------------------------------------------------------------------
| Store — the order-flow contract (v0.1, account-based checkout)
|--------------------------------------------------------------------------
| Browsing and the cart are guest-friendly; checkout requires an
| account. A guest hitting checkout is bounced to login (with the
| checkout notice), Laravel's intended-URL brings them back, the
| guest-filled cart survives the login, and the order snapshots the
| ACCOUNT identity (never form input). Confirmation is owner-only.
| The admin then finds the order in the panel and walks it through the
| lifecycle (confirm + mark paid).
*/

use App\Models\User;
use App\Modules\Store\Models\Order;
use App\Modules\Store\Models\Product;
use Database\Seeders\PenovaCoreSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('account-based checkout creates an order and the admin manages its lifecycle', function () {
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

    $customer = User::create([
        'name' => 'مشتری تستی',
        'email' => 'customer@example.com',
        'password' => Hash::make('Secret123!'),
    ]);

    // 1) Storefront + cart stay guest-friendly (zero friction).
    $this->get('/store')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Modules/Store/Storefront/Index')
            ->has('products.data', 1)); // only the active product

    $this->post('/store/cart/add', ['product_id' => $product->id])->assertRedirect();
    $this->post('/store/cart/add', ['product_id' => $product->id])->assertRedirect();
    $this->post('/store/cart/add', ['product_id' => $inactive->id])->assertSessionHasErrors('product_id');

    // 2) Checkout is auth-only: the guest is sent to login, and the
    //    login page knows why (checkout notice).
    $this->get('/store/checkout')->assertRedirect(route('login'));

    $this->get(route('login'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Core/Auth/Login')
            ->where('checkoutIntent', true));

    // 3) Logging in returns the user to checkout (intended URL), and
    //    the guest-filled cart survives the login.
    $this->post('/login', [
        'email' => 'customer@example.com',
        'password' => 'Secret123!',
    ])->assertRedirect('/store/checkout');

    $this->get('/store/checkout')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Modules/Store/Checkout/Index')
            ->where('account.name', 'مشتری تستی')
            ->has('lines', 1)
            ->where('lines.0.quantity', 2)
            // 301.0 serialises to the JSON integer 301.
            ->where('total', 301));

    // 4) Place the order. Identity/total in the payload must be ignored
    //    — both snapshot server-side (account + live cart math).
    $this->post('/store/checkout', [
        'customer_phone' => '09120000000',
        'shipping_address' => 'تهران، خیابان تست، پلاک ۱',
        'notes' => 'لطفاً صبح ارسال شود.',
        'customer_name' => 'جعلی',
        'customer_email' => 'fake@example.com',
        'total' => 1,
    ])->assertRedirect();

    $order = Order::with('items')->firstOrFail();

    expect($order->user_id)->toBe($customer->id)
        ->and($order->customer_name)->toBe('مشتری تستی') // account snapshot
        ->and($order->customer_email)->toBe('customer@example.com')
        ->and($order->status)->toBe('pending')
        ->and($order->payment_status)->toBe('unpaid')
        ->and((float) $order->total)->toBe(301.0)
        ->and($order->number)->toStartWith('ORD-')
        ->and($order->items)->toHaveCount(1)
        ->and($order->items[0]->product_name)->toBe('محصول تستی') // snapshot
        ->and((float) $order->items[0]->price)->toBe(150.5)
        ->and($order->items[0]->quantity)->toBe(2);

    // 5) Confirmation is owner-only; the emptied cart bounces checkout
    //    back to the storefront.
    $this->get("/store/orders/{$order->number}/confirmation")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Modules/Store/Checkout/Confirmation')
            ->where('order.number', $order->number));

    $this->get('/store/checkout')->assertRedirect(route('store.front'));

    // A customer without store permissions never reaches admin orders.
    $this->get('/workspace/store/orders')->assertForbidden();

    // Another account cannot read someone else's confirmation.
    User::create([
        'name' => 'دیگری',
        'email' => 'other@example.com',
        'password' => Hash::make('Secret123!'),
    ]);
    $this->post('/logout');
    $this->post('/login', ['email' => 'other@example.com', 'password' => 'Secret123!']);
    $this->get("/store/orders/{$order->number}/confirmation")->assertNotFound();

    // 6) Admin side: list (with the owning account), detail, lifecycle.
    $this->post('/logout');
    $this->post('/login', [
        'email' => config('penova.operator.email'),
        'password' => config('penova.operator.password'),
    ]);

    $this->get('/workspace/store/orders')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Modules/Store/Orders/Index')
            ->where('orders.data.0.number', $order->number)
            ->where('orders.data.0.user_name', 'مشتری تستی'));

    $this->get("/workspace/store/orders/{$order->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Modules/Store/Orders/Show')
            ->where('order.user.id', $customer->id)
            ->where('order.total', $order->total));

    $this->put("/workspace/store/orders/{$order->id}", ['status' => 'confirmed'])->assertRedirect();
    $this->put("/workspace/store/orders/{$order->id}", ['payment_status' => 'paid'])->assertRedirect();

    $order->refresh();
    expect($order->status)->toBe('confirmed')
        ->and($order->payment_status)->toBe('paid');

    // Lifecycle-only contract: unknown statuses are rejected.
    $this->put("/workspace/store/orders/{$order->id}", ['status' => 'shipped'])->assertSessionHasErrors('status');
});
