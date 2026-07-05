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
