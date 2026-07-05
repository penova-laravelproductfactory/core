<?php

namespace App\Modules\Store\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Store\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Modules\Store — the products list page (store.products.index).
 * Plain pagination; swap in Core's DataTableBuilder when the list
 * needs search/sort.
 */
class ProductIndexController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Modules/Store/Products/Index', [
            'products' => Product::latest()->paginate(10),
        ]);
    }
}
