<?php

namespace App\Modules\Store\Controllers;

use App\Core\DataTable\DataTableBuilder;
use App\Http\Controllers\Controller;
use App\Modules\Store\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Modules\Store — the products list page (store.products.index).
 *
 * Full DataTable contract (?search, ?sort, ?direction, ?page) plus two
 * module filters (?type, ?status) applied before the builder — the Vue
 * side passes them through DataTable's `params` prop so they survive
 * search/sort reloads and pagination links (withQueryString).
 */
class ProductIndexController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $query = Product::query()
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->query('type')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->query('status') === 'active'));

        return Inertia::render('Modules/Store/Products/Index', [
            'products' => DataTableBuilder::for($query)
                ->searchable(['name', 'slug', 'sku'])
                ->sortable(['name', 'type', 'price', 'updated_at'])
                // Daily product management is "what did we touch last?" —
                // recently edited beats recently created as the default.
                ->defaultSort('updated_at')
                ->paginate($request)
                ->through(fn (Product $product) => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'type' => $product->type,
                    'price' => $product->price,
                    'stock' => $product->stock,
                    'is_active' => $product->is_active,
                    'updated_at_human' => $product->updated_at?->format('Y-m-d H:i'),
                ]),

            // Echo the active filters so selects initialise correctly on
            // reload / shared links.
            'filters' => [
                'search' => (string) $request->query('search', ''),
                'type' => $request->query('type'),
                'status' => $request->query('status'),
            ],
        ]);
    }
}
