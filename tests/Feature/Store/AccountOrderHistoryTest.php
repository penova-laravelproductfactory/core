<?php

/*
|--------------------------------------------------------------------------
| Store — customer order history (v0.1)
|--------------------------------------------------------------------------
| A logged-in customer sees ONLY their own orders. Detail access is
| owner-scoped by {number} and 404s (never 403) for anyone else, so an
| order number is not an existence oracle. Guests are sent to login.
*/

use App\Models\User;
use App\Modules\Store\Models\Order;
use App\Modules\Store\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

function makeUser(string $email): User
{
    return User::create([
        'name' => "کاربر {$email}",
        'email' => $email,
        'password' => Hash::make('Secret123!'),
    ]);
}

function makeOrderFor(User $user): Order
{
    $order = Order::create([
        'user_id' => $user->id,
        'customer_name' => $user->name,
        'customer_email' => $user->email,
        'shipping_address' => 'تهران، خیابان تست، پلاک ۱',
        'total' => 100,
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_name' => 'محصول تستی',
        'price' => 100,
        'quantity' => 1,
        'subtotal' => 100,
    ]);

    return $order;
}

test('guest is redirected to login from order history', function () {
    $this->get('/store/account/orders')->assertRedirect(route('login'));
    $this->get('/store/account/orders/ORD-000000-XXXX')->assertRedirect(route('login'));
});

test('index lists only the current user orders', function () {
    $me = makeUser('me@example.com');
    $other = makeUser('other@example.com');

    makeOrderFor($me);
    makeOrderFor($me);
    makeOrderFor($other); // must NOT appear

    $this->actingAs($me)
        ->get('/store/account/orders')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Modules/Store/Account/Orders/Index')
            ->has('orders.data', 2)
            ->where('orders.data.0.number', fn ($n) => str_starts_with($n, 'ORD-'))
            ->where('orders.data.0.items_count', 1));
});

test('show returns the order for its owner', function () {
    $me = makeUser('me@example.com');
    $order = makeOrderFor($me);

    $this->actingAs($me)
        ->get("/store/account/orders/{$order->number}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Modules/Store/Account/Orders/Show')
            ->where('order.number', $order->number)
            ->has('order.items', 1)
            ->where('order.items.0.product_name', 'محصول تستی'));
});

test('show 404s (never 403) for another users order', function () {
    $me = makeUser('me@example.com');
    $other = makeUser('other@example.com');
    $theirs = makeOrderFor($other);

    $response = $this->actingAs($me)->get("/store/account/orders/{$theirs->number}");

    $response->assertNotFound();       // 404
    expect($response->status())->toBe(404); // explicitly NOT 403
});
