<?php

namespace App\Modules\Store\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Store\Models\Product;
use App\Modules\Store\Requests\StoreProductRequest;
use Illuminate\Http\RedirectResponse;

/**
 * Modules\Store — persists a new product (store.products.store).
 */
class ProductStoreController extends Controller
{
    public function __invoke(StoreProductRequest $request): RedirectResponse
    {
        Product::create($request->validated());

        return redirect()->route('store.products.index')->with('success', __('Product created.'));
    }
}
