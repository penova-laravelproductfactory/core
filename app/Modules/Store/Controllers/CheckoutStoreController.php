<?php

namespace App\Modules\Store\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Store\Models\Order;
use App\Modules\Store\Requests\PlaceOrderRequest;
use App\Modules\Store\Support\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

/**
 * Modules\Store — places the order (store.checkout.store), auth
 * required.
 *
 * Identity comes from the ACCOUNT (user_id + name/email snapshot),
 * never from the form. Totals and line prices come from the LIVE cart
 * resolution (current DB prices), never from the request; order +
 * items are written in one transaction and the cart is cleared only
 * after it commits.
 */
class CheckoutStoreController extends Controller
{
    public function __invoke(PlaceOrderRequest $request): RedirectResponse
    {
        $lines = Cart::lines();

        if ($lines->isEmpty()) {
            return redirect()->route('store.front');
        }

        $order = DB::transaction(function () use ($request, $lines) {
            $order = Order::create([
                ...$request->validated(),
                'user_id' => $request->user()->id,
                'customer_name' => $request->user()->name, // account snapshot
                'customer_email' => $request->user()->email, // account snapshot
                'total' => round($lines->sum('subtotal'), 2),
            ]);

            foreach ($lines as $line) {
                $order->items()->create([
                    'product_id' => $line['product']->id,
                    'product_name' => $line['product']->name, // snapshot
                    'price' => $line['product']->price, // snapshot
                    'quantity' => $line['quantity'],
                    'subtotal' => $line['subtotal'],
                ]);
            }

            return $order;
        });

        Cart::clear();

        return redirect()->route('store.checkout.confirmation', $order->number);
    }
}
