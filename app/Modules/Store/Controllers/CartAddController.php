<?php

namespace App\Modules\Store\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Store\Support\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Modules\Store — adds a product to the session cart (store.cart.add).
 * Adding the same product again increments its quantity.
 */
class CartAddController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => [
                'required', 'integer',
                Rule::exists('store_products', 'id')->where('is_active', true),
            ],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:999'],
        ]);

        Cart::add((int) $validated['product_id'], (int) ($validated['quantity'] ?? 1));

        return back();
    }
}
