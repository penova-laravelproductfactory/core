<?php

namespace App\Modules\Store\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Store\Support\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Modules\Store — removes a product line from the session cart
 * (store.cart.remove).
 */
class CartRemoveController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer'],
        ]);

        Cart::remove((int) $validated['product_id']);

        return back();
    }
}
