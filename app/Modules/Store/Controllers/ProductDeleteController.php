<?php

namespace App\Modules\Store\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Store\Models\Product;
use Illuminate\Http\RedirectResponse;

/**
 * Modules\Store — deletes a product (store.products.destroy).
 * Hard delete for Lite; switch the model to SoftDeletes when order
 * history needs to reference removed products.
 */
class ProductDeleteController extends Controller
{
    public function __invoke(Product $product): RedirectResponse
    {
        $product->delete();

        return back()->with('success', __('Product deleted.'));
    }
}
