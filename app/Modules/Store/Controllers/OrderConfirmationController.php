<?php

namespace App\Modules\Store\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Store\Models\Order;
use App\Modules\Store\Models\OrderItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Modules\Store — order confirmation (store.checkout.confirmation).
 * Auth + owner-only: orders belong to accounts now, so the page is
 * scoped to the user who placed it (404 for anyone else — no oracle
 * for probing order numbers).
 */
class OrderConfirmationController extends Controller
{
    public function __invoke(Request $request, string $number): Response
    {
        $order = Order::where('number', $number)
            ->where('user_id', $request->user()->id)
            ->with('items')
            ->firstOrFail();

        return Inertia::render('Modules/Store/Checkout/Confirmation', [
            'order' => [
                'number' => $order->number,
                'customer_name' => $order->customer_name,
                'total' => $order->total,
                'created_at' => $order->created_at->format('Y-m-d H:i'),
                'items' => $order->items->map(fn (OrderItem $item) => [
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->subtotal,
                ]),
            ],
        ]);
    }
}
