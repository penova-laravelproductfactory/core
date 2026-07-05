<?php

namespace App\Modules\Store\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Store\Support\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Modules\Store — the one-page checkout (store.checkout.show), auth
 * required: cart summary + account block (read-only name/email — the
 * order snapshots the account, v0.1 keeps identity single-sourced) +
 * shipping form. An empty cart bounces back to the storefront.
 */
class CheckoutShowController extends Controller
{
    public function __invoke(Request $request): Response|RedirectResponse
    {
        $lines = Cart::lines();

        if ($lines->isEmpty()) {
            return redirect()->route('store.front');
        }

        return Inertia::render('Modules/Store/Checkout/Index', [
            // Read-only identity shown on the page; the POST snapshots
            // the same values server-side (never trusted from the form).
            'account' => [
                'name' => $request->user()->name,
                'email' => $request->user()->email,
            ],
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
