<?php

namespace App\Modules\Store\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Store\Models\Product;
use App\Modules\Store\Models\ProductType;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Modules\Store — the "edit product" form page (store.products.edit).
 */
class ProductEditController extends Controller
{
    public function __invoke(Product $product): Response
    {
        return Inertia::render('Modules/Store/Products/Edit', [
            'product' => $product,
            'types' => ProductType::values(),
        ]);
    }
}
