# Order History (Account) — Design Spec (v0.1)

**Module:** `Modules/Store`
**Date:** 2026-07-06
**Status:** Approved for implementation
**Stack:** Laravel 12 · Inertia 2 · Vue 3 · Tailwind 4 (Persian / RTL, currency تومان)

## Goal

Let a logged-in customer see the orders they have placed: a reverse-chronological
overview list and a read-only detail page for a single order. No admin surface is
touched — this is the customer-facing counterpart to the existing admin order pages.

## Context (verified against the codebase)

- Orders already exist and are **account-bound**: `store_orders.user_id` is `NOT NULL`
  (`restrictOnDelete`); `customer_name/email/phone`, `shipping_address` and each
  `order_item.product_name/price` are **placement-time snapshots**. No schema work.
- The customer order flow already lives under `/store/*` (`store.*` names) in
  `app/Modules/Store/routes.public.php`, inside an `auth` group. `OrderConfirmationController`
  is the reference for **owner-scoped** access.
- `Modules/Store/Pages/Orders/{Index,Show}.vue` already exist but are the **admin** pages.
  Customer pages therefore live under `Account/Orders/` to avoid collision.
- Customer chrome is `StorefrontLayout.vue` (minimal Persian/RTL brand bar) — **never**
  `AdminLayout`. `HandleInertiaRequests` already shares `auth.user`.
- Reuse the admin pages' status vocabulary and `formatPrice`:
  - Labels: `pending→در انتظار`, `confirmed→تأییدشده`, `completed→تکمیل‌شده`, `cancelled→لغوشده`.
  - Classes: `pending→bg-amber-100 text-amber-700`, `confirmed→bg-sky-100 text-sky-700`,
    `completed→bg-green-100 text-green-700`, `cancelled→bg-red-100 text-red-700`.
  - Payment: `paid→پرداخت‌شده (green)`, `unpaid→پرداخت‌نشده (slate)`.
  - `formatPrice = (p) => Number(p ?? 0).toLocaleString('fa-IR')`.

## Decisions (resolved during brainstorming)

- **Namespace:** `/store/account/orders`, names `store.account.orders.*` — stays inside
  the Store module rather than inventing a Core-level `account.*` namespace.
- **Chrome:** extend `StorefrontLayout` with an authenticated account nav (no new
  `AccountLayout`). The account nav is written as an **extensible slot** so future
  entries (پروفایل، آدرس‌ها) drop in without refactor.
- **Read-only v0.1:** no cancel / reorder / invoice / filters / search. Pagination only.

## 1. UX

**Pattern:** reverse-chronological list of **cards** (newest first). Fits a consumer's
short (~5–15 order) history and how they recall purchases; density is an admin need,
already served by the DataTable admin list.

**Overview card — recognition set only:** order number, date, **status badge**,
**payment badge**, total (labelled), item count, and a single «مشاهدهٔ جزئیات» CTA.

- Badge **positions are fixed**: status at the top-start, payment at the top-end, so
  color + position give an at-a-glance scan across cards.
- Total is always **labelled**: «جمع کل: ۱۲۳٬۰۰۰ تومان» — never a bare number.
- No thumbnails, no inline items — those belong on the detail page.
- Card + detail layouts leave a natural slot for a future «سفارش مجدد» (re-order) CTA,
  the likely first follow-up feature.

**Empty state:** friendly «هنوز سفارشی ثبت نکرده‌اید» card linking back to `/store`.

**Deferred (YAGNI):** reorder, returns/cancellation, invoice download, filters/search.
Pagination alone (10/page) is sufficient wayfinding for this audience; reorder/returns
need cart/refund pipelines that don't exist yet, and an action the backend can't fulfill
erodes trust more than its absence.

## 2. Routes & Controller

Added to `app/Modules/Store/routes.public.php`, inside the existing `auth` group:

```php
Route::middleware('auth')->group(function () {
    // ... existing checkout + confirmation ...
    Route::get('/store/account/orders', [AccountOrderController::class, 'index'])
        ->name('store.account.orders.index');
    Route::get('/store/account/orders/{number}', [AccountOrderController::class, 'show'])
        ->name('store.account.orders.show');
});
```

**`AccountOrderController`** — a single two-method controller (`index` + `show` are the
same resource; grouping is clearer than two invokables here).

- `index(Request)` — `Order::where('user_id', $request->user()->id)->latest()
  ->withCount('items')->paginate(10)`, mapped `->through(...)` to the index DTO.
- `show(Request, string $number)` — bind by **`{number}`** (the human reference the
  customer already holds), scoped to owner exactly like the confirmation controller:
  `Order::where('number', $number)->where('user_id', $request->user()->id)
  ->with('items')->firstOrFail()`.

**Security note:** access control is the **owner-scoped query**, not the secrecy of the
order number. `number` (`ORD-ymd-XXXX`, uniqueness-checked, non-sequential) is used only
to keep the URL on a human reference. A non-owner (or unknown number) gets **404, never
403** — no oracle for probing whether another user's order exists.

### DTOs

`index` (per order):

```
{ number, status, payment_status, total, items_count, created_at }
```

`show`:

```
{
  number, status, payment_status, total, created_at,
  customer_name, customer_email, customer_phone, shipping_address, notes,
  items: [ { product_name, price, quantity, subtotal } ]
}
```

`created_at` formatted `->format('Y-m-d H:i')` — matching the confirmation and admin
order controllers — and rendered `dir="ltr"` alongside the order number.

## 3. Pages

### `resources/js/Modules/Store/Pages/Account/Orders/Index.vue`
Composes extended `StorefrontLayout`. Renders order cards from `orders.data` (fixed badge
positions, labelled total, «مشاهدهٔ جزئیات» → `store.account.orders.show`), then Core
`<Pagination :links="orders.links" />`. Empty state as above. Reuses the status maps and
`formatPrice` verbatim.

### `resources/js/Modules/Store/Pages/Account/Orders/Show.vue`
Composes extended `StorefrontLayout`. Three blocks:

1. **Summary header** — number, date, status + payment badges, total.
2. **Items** — `product_name`, `quantity`, `price`, `subtotal` (snapshots).
3. **Delivery & info** — `shipping_address`, `notes` (if present), and the
   `customer_name/email/phone` snapshot for clarity.

No actions (read-only). A «بازگشت به سفارش‌ها» link back to the index is the only nav.

## 4. Navigation

In `StorefrontLayout.vue`, when `usePage().props.auth.user` is truthy, the brand bar shows
an account nav — «سفارش‌های من» (→ `store.account.orders.index`) and a logout control —
beside the existing cart link. Structured as an extensible list so future account entries
slot in. Always available to a logged-in user from any storefront page; order history is a
standing affordance, not a post-checkout-only link.

## 5. Security & Core alignment

- Both actions filter `user_id = auth id` **in the query** — never `find()`-then-compare.
- `show` → `where(number)->where(user_id)->firstOrFail()` → **404** for non-owners.
- Core/Module boundary intact: reuse Core `auth` middleware, `Pagination`, shared
  `auth.user`, and the Inertia render convention — **no Core changes**. Auth/users stay
  Core; orders stay `Modules/Store`.

## 6. Testing

Feature tests in the spirit of `tests/Feature/Store/OrderFlowTest.php`:

- A user's index lists **only their own** orders (another user's order absent).
- `show` on another user's order returns **404** (assert status 404 explicitly — never
  200 and never 403).
- A guest hitting either route is redirected to `login`.

## Out of scope (v0.1)

Reorder, returns/cancellation, invoice/PDF, filters/search, per-order status timeline,
and any admin-side change.
