<?php

namespace App\Modules\Store\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Store\Models\Product;
use Illuminate\Http\JsonResponse;

/**
 * Modules\Store — tiny JSON endpoint backing the widget
 * (store.products.active-count): how many products are active.
 *
 * Response shape is guaranteed: { "count": number }.
 * ActiveProductsCard.vue fetches this on mount.
 */
class ActiveProductsCountController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'count' => Product::where('is_active', true)->count(),
        ]);
    }
}
