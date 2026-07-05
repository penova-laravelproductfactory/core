# Order History (Account) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Let a logged-in customer view their own placed orders — a reverse-chronological overview list and a read-only detail page — under the Store module's customer-facing surface.

**Architecture:** Two new `store.account.orders.*` routes in the existing `auth` group of `routes.public.php`, served by a single `AccountOrderController` (index + show). Both actions filter `user_id = auth id` in the query; `show` binds by `{number}` and `firstOrFail()`s to a 404 for non-owners. Two Vue pages under `Account/Orders/` (kept separate from the admin `Orders/` pages) compose an extended `StorefrontLayout` that gains an authenticated account nav.

**Tech Stack:** Laravel 12, Inertia 2, Vue 3, Tailwind 4. Tests: Pest feature tests. Persian/RTL UI, currency تومان.

## Global Constraints

- **No AI attribution** in any commit message or file (repo policy — history was scrubbed).
- **Persian/RTL** copy throughout; order numbers and dates render `dir="ltr"`.
- **Currency:** `formatPrice = (p) => Number(p ?? 0).toLocaleString('fa-IR')`, suffix تومان.
- **Owner-scoping is the access control** — never `find()`-then-compare; non-owner → **404, never 403**.
- **No Core changes** — reuse Core `auth` middleware, `Pagination`, shared `auth.user`.
- **Read-only v0.1** — no cancel/reorder/invoice/filters/search.
- Status maps reused verbatim from the admin pages:
  - Labels: `pending→در انتظار`, `confirmed→تأییدشده`, `completed→تکمیل‌شده`, `cancelled→لغوشده`.
  - Classes: `pending→bg-amber-100 text-amber-700`, `confirmed→bg-sky-100 text-sky-700`, `completed→bg-green-100 text-green-700`, `cancelled→bg-red-100 text-red-700`.
  - Payment: `paid→پرداخت‌شده` (`bg-green-100 text-green-700`), `unpaid→پرداخت‌نشده` (`bg-slate-100 text-slate-500`).

## File Structure

- Create: `app/Modules/Store/Controllers/AccountOrderController.php` — index + show, owner-scoped DTOs.
- Modify: `app/Modules/Store/routes.public.php` — import + two routes in the `auth` group.
- Create: `tests/Feature/Store/AccountOrderHistoryTest.php` — owner-scoping, 404, guest redirect.
- Create: `resources/js/Modules/Store/Pages/Account/Orders/Index.vue` — overview cards + pagination.
- Create: `resources/js/Modules/Store/Pages/Account/Orders/Show.vue` — read-only detail.
- Modify: `resources/js/Modules/Store/Components/StorefrontLayout.vue` — authenticated account nav.

---

### Task 1: Backend — routes, controller, feature tests

**Files:**
- Create: `app/Modules/Store/Controllers/AccountOrderController.php`
- Modify: `app/Modules/Store/routes.public.php`
- Test: `tests/Feature/Store/AccountOrderHistoryTest.php`

**Interfaces:**
- Consumes: `App\Modules\Store\Models\Order` (`where('user_id')`, `where('number')`, `items()` relation, `withCount('items')`), `App\Modules\Store\Models\OrderItem`.
- Produces:
  - Route names `store.account.orders.index` (`GET /store/account/orders`) and `store.account.orders.show` (`GET /store/account/orders/{number}`).
  - Inertia component `Modules/Store/Account/Orders/Index` with prop `orders` = Laravel paginator whose each `data` row is `{ number, status, payment_status, total, items_count, created_at }`.
  - Inertia component `Modules/Store/Account/Orders/Show` with prop `order` = `{ number, status, payment_status, total, created_at, customer_name, customer_email, customer_phone, shipping_address, notes, items: [{ product_name, price, quantity, subtotal }] }`.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Store/AccountOrderHistoryTest.php`:

```php
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

    $mine = makeOrderFor($me);
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
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Store/AccountOrderHistoryTest.php`
Expected: FAIL — routes `/store/account/orders` return 404 (not registered yet).

- [ ] **Step 3: Create the controller**

Create `app/Modules/Store/Controllers/AccountOrderController.php`:

```php
<?php

namespace App\Modules\Store\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Store\Models\Order;
use App\Modules\Store\Models\OrderItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Modules\Store — customer order history (store.account.orders.*).
 *
 * Owner-scoped end to end: every query filters user_id = auth id, so a
 * customer only ever sees their own orders. show() binds by {number}
 * (the human reference the customer already holds) and firstOrFail()s
 * to a 404 — never 403 — for a non-owner, so an order number is not an
 * existence oracle. Access control is the query, not the URL's secrecy.
 *
 * Read-only in v0.1: no cancel / reorder / invoice actions (the backend
 * has no pipeline for them yet; an action it can't honour erodes trust).
 */
class AccountOrderController extends Controller
{
    public function index(Request $request): Response
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->withCount('items')
            ->latest()
            ->paginate(10)
            ->through(fn (Order $order) => [
                'number' => $order->number,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'total' => $order->total,
                'items_count' => $order->items_count,
                'created_at' => $order->created_at->format('Y-m-d H:i'),
            ]);

        return Inertia::render('Modules/Store/Account/Orders/Index', [
            'orders' => $orders,
        ]);
    }

    public function show(Request $request, string $number): Response
    {
        $order = Order::where('number', $number)
            ->where('user_id', $request->user()->id)
            ->with('items')
            ->firstOrFail();

        return Inertia::render('Modules/Store/Account/Orders/Show', [
            'order' => [
                'number' => $order->number,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'total' => $order->total,
                'created_at' => $order->created_at->format('Y-m-d H:i'),
                'customer_name' => $order->customer_name,
                'customer_email' => $order->customer_email,
                'customer_phone' => $order->customer_phone,
                'shipping_address' => $order->shipping_address,
                'notes' => $order->notes,
                'items' => $order->items->map(fn (OrderItem $item) => [
                    'product_name' => $item->product_name,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->subtotal,
                ]),
            ],
        ]);
    }
}
```

- [ ] **Step 4: Register the routes**

In `app/Modules/Store/routes.public.php`, add the import near the other controller imports:

```php
use App\Modules\Store\Controllers\AccountOrderController;
```

Then inside the existing `Route::middleware('auth')->group(function () { ... })` block, after the confirmation route, add:

```php
    // Customer order history — owner-scoped in the controller (index
    // filters by user_id; show 404s for a non-owner).
    Route::get('/store/account/orders', [AccountOrderController::class, 'index'])->name('store.account.orders.index');
    Route::get('/store/account/orders/{number}', [AccountOrderController::class, 'show'])->name('store.account.orders.show');
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test tests/Feature/Store/AccountOrderHistoryTest.php`
Expected: PASS — 4 tests green.

- [ ] **Step 6: Commit**

```bash
git add app/Modules/Store/Controllers/AccountOrderController.php app/Modules/Store/routes.public.php tests/Feature/Store/AccountOrderHistoryTest.php
git commit -m "feat(store): customer order history endpoints (owner-scoped)"
```

---

### Task 2: Overview page — `Account/Orders/Index.vue`

**Files:**
- Create: `resources/js/Modules/Store/Pages/Account/Orders/Index.vue`

**Interfaces:**
- Consumes: prop `orders` (paginator: `{ data: [{ number, status, payment_status, total, items_count, created_at }], links }`) from Task 1; Core `@/Core/Components/Pagination.vue` (prop `links`).
- Produces: the page rendered for component `Modules/Store/Account/Orders/Index`; card CTA links to `/store/account/orders/{number}` (consumed by Task 1's `show`).

- [ ] **Step 1: Create the page**

Create `resources/js/Modules/Store/Pages/Account/Orders/Index.vue`:

```vue
<script setup>
/**
 * Modules\Store — customer order history overview. Reverse-chronological
 * cards; recognition set only (number, date, status, payment, total,
 * item count) + a details CTA. Badge positions are fixed (status start,
 * payment end) so colour + position scan at a glance. Composes the
 * storefront chrome — customers never see AdminLayout.
 */
import StorefrontLayout from '@/Modules/Store/Components/StorefrontLayout.vue';
import Pagination from '@/Core/Components/Pagination.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    orders: Object, // Laravel paginator: { data, links, meta }
});

const statusLabels = {
    pending: 'در انتظار',
    confirmed: 'تأییدشده',
    completed: 'تکمیل‌شده',
    cancelled: 'لغوشده',
};

const statusClasses = {
    pending: 'bg-amber-100 text-amber-700',
    confirmed: 'bg-sky-100 text-sky-700',
    completed: 'bg-green-100 text-green-700',
    cancelled: 'bg-red-100 text-red-700',
};

const formatPrice = (price) => Number(price ?? 0).toLocaleString('fa-IR');
</script>

<template>
    <StorefrontLayout title="سفارش‌های من">
        <h1 class="mb-6 text-xl font-bold text-slate-900">سفارش‌های من</h1>

        <div v-if="orders.data.length" class="space-y-4">
            <article
                v-for="order in orders.data"
                :key="order.number"
                class="rounded-lg border border-slate-200 bg-white p-4"
            >
                <!-- Fixed badge positions: status at start, payment at end. -->
                <div class="flex items-start justify-between">
                    <span
                        class="rounded px-2 py-0.5 text-xs font-medium"
                        :class="statusClasses[order.status] ?? 'bg-slate-100 text-slate-600'"
                    >
                        {{ statusLabels[order.status] ?? order.status }}
                    </span>
                    <span
                        class="rounded px-2 py-0.5 text-xs font-medium"
                        :class="order.payment_status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500'"
                    >
                        {{ order.payment_status === 'paid' ? 'پرداخت‌شده' : 'پرداخت‌نشده' }}
                    </span>
                </div>

                <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <div class="font-mono text-sm font-bold text-slate-900" dir="ltr">{{ order.number }}</div>
                        <div class="mt-1 text-xs text-slate-400" dir="ltr">{{ order.created_at }}</div>
                    </div>
                    <div class="text-sm text-slate-600">{{ order.items_count }} قلم</div>
                </div>

                <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3">
                    <span class="text-sm text-slate-700">
                        جمع کل: <span class="font-bold text-slate-900">{{ formatPrice(order.total) }} تومان</span>
                    </span>
                    <Link
                        :href="`/store/account/orders/${order.number}`"
                        class="rounded-md bg-brand px-3 py-1.5 text-sm font-semibold text-white hover:bg-brand-hover"
                    >
                        مشاهدهٔ جزئیات
                    </Link>
                </div>
            </article>

            <Pagination :links="orders.links" />
        </div>

        <div v-else class="rounded-lg border border-slate-200 bg-white p-8 text-center">
            <p class="text-slate-500">هنوز سفارشی ثبت نکرده‌اید.</p>
            <Link href="/store" class="mt-3 inline-block text-brand hover:underline">رفتن به فروشگاه</Link>
        </div>
    </StorefrontLayout>
</template>
```

- [ ] **Step 2: Verify the build compiles**

Run: `npm run build`
Expected: build succeeds; `Account/Orders/Index` code-split chunk emitted, no Vue compile errors.

- [ ] **Step 3: Commit**

```bash
git add resources/js/Modules/Store/Pages/Account/Orders/Index.vue
git commit -m "feat(store): order history overview page"
```

---

### Task 3: Detail page — `Account/Orders/Show.vue`

**Files:**
- Create: `resources/js/Modules/Store/Pages/Account/Orders/Show.vue`

**Interfaces:**
- Consumes: prop `order` = `{ number, status, payment_status, total, created_at, customer_name, customer_email, customer_phone, shipping_address, notes, items: [{ product_name, price, quantity, subtotal }] }` from Task 1.
- Produces: the page rendered for component `Modules/Store/Account/Orders/Show`; back link to `/store/account/orders`.

- [ ] **Step 1: Create the page**

Create `resources/js/Modules/Store/Pages/Account/Orders/Show.vue`:

```vue
<script setup>
/**
 * Modules\Store — customer order detail (read-only, v0.1). Summary
 * header, item snapshots, delivery/info. No actions: cancel/reorder need
 * backend pipelines that don't exist yet. Item names/prices are the
 * placement-time snapshot — the order reads as it was bought.
 */
import StorefrontLayout from '@/Modules/Store/Components/StorefrontLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    order: Object,
});

const statusLabels = {
    pending: 'در انتظار',
    confirmed: 'تأییدشده',
    completed: 'تکمیل‌شده',
    cancelled: 'لغوشده',
};

const statusClasses = {
    pending: 'bg-amber-100 text-amber-700',
    confirmed: 'bg-sky-100 text-sky-700',
    completed: 'bg-green-100 text-green-700',
    cancelled: 'bg-red-100 text-red-700',
};

const formatPrice = (price) => Number(price ?? 0).toLocaleString('fa-IR');
</script>

<template>
    <StorefrontLayout title="جزئیات سفارش">
        <Link href="/store/account/orders" class="text-sm text-brand hover:underline">→ بازگشت به سفارش‌ها</Link>

        <!-- Summary -->
        <div class="mt-4 rounded-lg border border-slate-200 bg-white p-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <div class="font-mono text-lg font-bold text-slate-900" dir="ltr">{{ order.number }}</div>
                    <div class="mt-1 text-xs text-slate-400" dir="ltr">{{ order.created_at }}</div>
                </div>
                <div class="flex gap-2">
                    <span class="rounded px-2 py-0.5 text-xs font-medium" :class="statusClasses[order.status] ?? 'bg-slate-100 text-slate-600'">
                        {{ statusLabels[order.status] ?? order.status }}
                    </span>
                    <span class="rounded px-2 py-0.5 text-xs font-medium" :class="order.payment_status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500'">
                        {{ order.payment_status === 'paid' ? 'پرداخت‌شده' : 'پرداخت‌نشده' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Items -->
        <div class="mt-4 rounded-lg border border-slate-200 bg-white p-4">
            <h2 class="mb-3 text-sm font-semibold text-slate-700">اقلام سفارش</h2>
            <ul class="divide-y divide-slate-100">
                <li v-for="(item, i) in order.items" :key="i" class="flex items-center justify-between py-2 text-sm">
                    <span class="text-slate-700">{{ item.product_name }} × {{ item.quantity }}</span>
                    <span class="text-slate-500">{{ formatPrice(item.subtotal) }} تومان</span>
                </li>
            </ul>
            <div class="mt-3 flex items-center justify-between border-t border-slate-200 pt-3">
                <span class="text-sm font-semibold text-slate-700">جمع کل</span>
                <span class="font-bold text-slate-900">{{ formatPrice(order.total) }} تومان</span>
            </div>
        </div>

        <!-- Delivery & info -->
        <div class="mt-4 rounded-lg border border-slate-200 bg-white p-4 text-sm">
            <h2 class="mb-3 text-sm font-semibold text-slate-700">اطلاعات ارسال</h2>
            <dl class="space-y-2 text-slate-600">
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-400">گیرنده</dt>
                    <dd class="text-start">{{ order.customer_name }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-400">ایمیل</dt>
                    <dd dir="ltr">{{ order.customer_email }}</dd>
                </div>
                <div v-if="order.customer_phone" class="flex justify-between gap-4">
                    <dt class="text-slate-400">تلفن</dt>
                    <dd dir="ltr">{{ order.customer_phone }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-400">نشانی</dt>
                    <dd class="text-start">{{ order.shipping_address }}</dd>
                </div>
                <div v-if="order.notes" class="flex justify-between gap-4">
                    <dt class="text-slate-400">یادداشت</dt>
                    <dd class="text-start">{{ order.notes }}</dd>
                </div>
            </dl>
        </div>
    </StorefrontLayout>
</template>
```

- [ ] **Step 2: Verify the build compiles**

Run: `npm run build`
Expected: build succeeds; `Account/Orders/Show` chunk emitted, no Vue compile errors.

- [ ] **Step 3: Commit**

```bash
git add resources/js/Modules/Store/Pages/Account/Orders/Show.vue
git commit -m "feat(store): order history detail page (read-only)"
```

---

### Task 4: Account nav in `StorefrontLayout.vue`

**Files:**
- Modify: `resources/js/Modules/Store/Components/StorefrontLayout.vue`

**Interfaces:**
- Consumes: shared Inertia prop `auth.user` (from `HandleInertiaRequests`); route `store.account.orders.index` (`/store/account/orders`) from Task 1; Core `logout` route (`POST /logout`).
- Produces: an authenticated account nav (extensible `accountLinks` array) usable by every storefront page.

- [ ] **Step 1: Add `user` + extensible nav to the script**

In `resources/js/Modules/Store/Components/StorefrontLayout.vue`, the script already imports `computed` and `usePage`. Add below the existing `appName` computed:

```js
const user = computed(() => usePage().props.auth?.user);

// Extensible account menu — future entries (پروفایل، آدرس‌ها) slot in
// here without touching the template.
const accountLinks = [
    { label: 'سفارش‌های من', href: '/store/account/orders' },
];
```

- [ ] **Step 2: Replace the header's action area with the nav**

Replace the single checkout `<Link>` in the header with this nav cluster (keep the existing brand `<Link href="/store">` untouched):

```vue
                <nav class="flex items-center gap-4 text-sm">
                    <template v-if="user">
                        <Link
                            v-for="link in accountLinks"
                            :key="link.href"
                            :href="link.href"
                            class="font-medium text-slate-600 hover:text-slate-900"
                        >
                            {{ link.label }}
                        </Link>
                        <Link
                            href="/logout"
                            method="post"
                            as="button"
                            class="text-slate-400 hover:text-slate-700"
                        >
                            خروج
                        </Link>
                    </template>

                    <Link
                        href="/store/checkout"
                        class="rounded-md bg-brand px-3 py-2 text-sm font-semibold text-white hover:bg-brand-hover"
                        :class="{ 'pointer-events-none opacity-40': cartCount === 0 }"
                    >
                        تسویه حساب ({{ cartCount }})
                    </Link>
                </nav>
```

- [ ] **Step 3: Verify the build compiles**

Run: `npm run build`
Expected: build succeeds, no Vue compile errors.

- [ ] **Step 4: Run the full Store suite to confirm nothing regressed**

Run: `php artisan test tests/Feature/Store`
Expected: PASS — the new `AccountOrderHistoryTest` plus the existing `OrderFlowTest` all green.

- [ ] **Step 5: Commit**

```bash
git add resources/js/Modules/Store/Components/StorefrontLayout.vue
git commit -m "feat(store): account nav in storefront layout"
```

---

## Self-Review

**Spec coverage:**
- §1 UX (cards, fixed badges, labelled total, empty state, deferred features) → Task 2. ✓
- §2 routes + owner-scoped controller + DTOs → Task 1. ✓
- §3 Index page → Task 2; Show page → Task 3. ✓
- §4 account nav (extensible) → Task 4. ✓
- §5 security (query-scoped, 404) → Task 1 controller + tests. ✓
- §6 tests (owner-only index, 404 non-owner, guest redirect) → Task 1 Step 1. ✓
- Re-order future CTA slot: cards/detail leave room (no blocking layout) → Tasks 2/3. ✓

**Placeholder scan:** none — every step has full code/commands.

**Type consistency:** `orders.data` row shape `{ number, status, payment_status, total, items_count, created_at }` matches between Task 1 controller and Task 2 page. `order` detail shape matches between Task 1 and Task 3. `accountLinks` shape `{ label, href }` consistent in Task 4. Route `/store/account/orders/{number}` used identically in controller, Index CTA, and Show back link.
