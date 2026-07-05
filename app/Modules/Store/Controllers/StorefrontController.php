<?php

namespace App\Modules\Store\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Store\Models\Product;
use App\Modules\Store\Support\Cart;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Modules\Store — the minimal public storefront (store.front): active
 * products with add-to-cart. Exists so the checkout flow has a real
 * entry point; a themed storefront replaces it per product later.
 */
class StorefrontController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Modules/Store/Storefront/Index', [
            'products' => Product::where('is_active', true)
                ->latest('updated_at')
                ->paginate(12)
                ->through(fn (Product $product) => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'type' => $product->type,
                    'price' => $product->price,
                    'description' => $product->description,
                ]),
            'cartCount' => Cart::count(),
        ]);
    }
}
