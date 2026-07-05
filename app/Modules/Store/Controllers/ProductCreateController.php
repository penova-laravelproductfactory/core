<?php

namespace App\Modules\Store\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Store\Models\ProductType;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Modules\Store — the "new product" form page (store.products.create).
 */
class ProductCreateController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Modules/Store/Products/Create', [
            'types' => ProductType::values(),
        ]);
    }
}
