<?php

namespace App\Modules\Store\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Store\Support\Cart;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Modules\Store — the one-page guest checkout (store.checkout.show):
 * cart summary + customer form. An empty cart bounces back to the
 * storefront — there is nothing to check out.
 */
class CheckoutShowController extends Controller
{
    public function __invoke(): Response|RedirectResponse
    {
        $lines = Cart::lines();

        if ($lines->isEmpty()) {
            return redirect()->route('store.front');
        }

        return Inertia::render('Modules/Store/Checkout/Index', [
            'lines' => $lines->map(fn (array $line) => [
                'product_id' => $line['product']->id,
                'name' => $line['product']->name,
                'price' => $line['product']->price,
                'quantity' => $line['quantity'],
                'subtotal' => $line['subtotal'],
            ]),
            'total' => Cart::total(),
            'cartCount' => Cart::count(),
        ]);
    }
}
