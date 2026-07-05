<?php

namespace App\Modules\Store\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Store\Models\Order;
use App\Modules\Store\Models\OrderItem;
use App\Modules\Store\Models\OrderStatus;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Modules\Store — admin order detail (store.orders.show): customer
 * block, items table, totals, and the two lifecycle actions.
 */
class OrderShowController extends Controller
{
    public function __invoke(Order $order): Response
    {
        $order->load(['items', 'user:id,name,email']);

        return Inertia::render('Modules/Store/Orders/Show', [
            'order' => [
                'id' => $order->id,
                'number' => $order->number,
                // Owning account (live data + link target for Core users).
                'user' => $order->user ? [
                    'id' => $order->user->id,
                    'name' => $order->user->name,
                    'email' => $order->user->email,
                ] : null,
                'customer_name' => $order->customer_name,
                'customer_email' => $order->customer_email,
                'customer_phone' => $order->customer_phone,
                'shipping_address' => $order->shipping_address,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'total' => $order->total,
                'notes' => $order->notes,
                'created_at' => $order->created_at->format('Y-m-d H:i'),
                'items' => $order->items->map(fn (OrderItem $item) => [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->subtotal,
                ]),
            ],
            'statuses' => OrderStatus::values(),
        ]);
    }
}
