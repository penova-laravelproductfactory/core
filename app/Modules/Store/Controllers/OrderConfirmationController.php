<?php

namespace App\Modules\Store\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Store\Models\Order;
use App\Modules\Store\Models\OrderItem;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Modules\Store — guest order confirmation (store.checkout.confirmation).
 * Access by order number only — v0.1 accepts "knows the number" as the
 * access model (numbers are random); accounts tighten this later.
 */
class OrderConfirmationController extends Controller
{
    public function __invoke(string $number): Response
    {
        $order = Order::where('number', $number)->with('items')->firstOrFail();

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
