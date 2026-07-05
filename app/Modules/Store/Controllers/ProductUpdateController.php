<?php

namespace App\Modules\Store\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Store\Models\Product;
use App\Modules\Store\Requests\UpdateProductRequest;
use Illuminate\Http\RedirectResponse;

/**
 * Modules\Store — applies edits to a product (store.products.update).
 */
class ProductUpdateController extends Controller
{
    public function __invoke(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->validated());

        return redirect()->route('store.products.index')->with('success', __('Product updated.'));
    }
}
