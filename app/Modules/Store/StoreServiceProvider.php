<?php

namespace App\Modules\Store;

use App\Core\Support\DeclaresFrontendSource;
use App\Core\Support\Manifest;
use App\Core\Support\PenovaModule;
use Illuminate\Contracts\Foundation\CachesRoutes;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Store — minimal but real e-commerce module: product management
 * (physical / virtual / downloadable) plus an "active products"
 * widget. Orders, cart and checkout build on this skeleton
 * in later versions.
 *
 * Data contract: ActiveProductsCard.vue (resources/js/Modules/Store/
 * Widgets) fetches GET <workspace-prefix>/store/products/active-count (route
 * "store.products.active-count"); the response is always
 * { count: number }.
 *
 * The module declares everything it contributes through one Manifest
 * (see manifest()); routes and migrations are provider mechanics, wired
 * in boot() — they are not Manifest contributions (D-023).
 */
class StoreServiceProvider extends ServiceProvider implements DeclaresFrontendSource, PenovaModule
{
    public function boot(): void
    {
        // Module routes live in routes.php as plain definitions; the
        // panel group (URI prefix + auth middleware) is applied here.
        // Cache guard mirrors loadRoutesFrom() so route:cache stays safe.
        if (! ($this->app instanceof CachesRoutes && $this->app->routesAreCached())) {
            // Workspace surface: store routes register under the configured
            // Workspace prefix (default /workspace/store/...), web + auth.
            Route::middleware(config('penova.workspace.middleware'))
                ->prefix(config('penova.workspace.prefix'))
                ->group(__DIR__.'/routes.php');

            // Public surface: /store/... — guest storefront + checkout,
            // session only ("web"), no auth.
            Route::middleware('web')->group(__DIR__.'/routes.public.php');
        }

        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
    }

    /**
     * The Store module's single Manifest — its complete declaration of
     * what it contributes to the Platform.
     */
    public static function manifest(): Manifest
    {
        return Manifest::for(
            key: 'store',
            name: 'Store',
            description: 'Products, orders and checkout — turn Core into a real store.',
            version: '0.1.0',
        )
            ->menu([
                ['key' => 'store', 'label' => 'فروشگاه', 'route' => 'store.products.index', 'icon' => 'bag', 'order' => 400, 'permission' => 'store.view'],
                ['key' => 'store-orders', 'label' => 'سفارش‌ها', 'route' => 'store.orders.index', 'icon' => 'clipboard', 'order' => 410, 'permission' => 'store.view'],
            ])
            ->widgets([
                ['key' => 'store-active-products', 'type' => 'card', 'title' => 'محصولات فعال', 'cols' => 1, 'order' => 400, 'area' => 'store', 'permission' => 'store.view'],
            ])
            ->permissions(['store.view', 'store.manage'])
            // EXPERIMENTAL frontend seam (RFC-006 / D-028): typed entry tokens
            // resolved through Store's OWN coordinate (declared via
            // frontendSource() below) — no component path-string.
            ->frontend([
                'widgets' => [
                    ['key' => 'store-active-products', 'entry' => 'Widgets/ActiveProductsCard'],
                ],
                'pages' => [
                    ['name' => 'Modules/Store/Products/Index', 'entry' => 'Pages/Products/Index'],
                    ['name' => 'Modules/Store/Products/Create', 'entry' => 'Pages/Products/Create'],
                    ['name' => 'Modules/Store/Products/Edit', 'entry' => 'Pages/Products/Edit'],
                    ['name' => 'Modules/Store/Orders/Index', 'entry' => 'Pages/Orders/Index'],
                    ['name' => 'Modules/Store/Orders/Show', 'entry' => 'Pages/Orders/Show'],
                    ['name' => 'Modules/Store/Account/Orders/Index', 'entry' => 'Pages/Account/Orders/Index'],
                    ['name' => 'Modules/Store/Account/Orders/Show', 'entry' => 'Pages/Account/Orders/Show'],
                    ['name' => 'Modules/Store/Checkout/Index', 'entry' => 'Pages/Checkout/Index'],
                    ['name' => 'Modules/Store/Checkout/Confirmation', 'entry' => 'Pages/Checkout/Confirmation'],
                    ['name' => 'Modules/Store/Storefront/Index', 'entry' => 'Pages/Storefront/Index'],
                ],
            ]);
    }

    /**
     * Store's OWN frontend coordinate root (EXPERIMENTAL — RFC-006 / D-028) —
     * module-owned build metadata, not a Manifest contribution. Store's frontend
     * lives in-repo under `resources/js/Modules/Store`, so its `entry` tokens
     * resolve against the `@/Modules/Store` import alias. (The Manifest key is the
     * lowercase `store`; the source is declared explicitly because the on-disk
     * directory is `Store` — the coordinate is the Module's to own, not Core's to
     * infer.) Once Store is physically relocated, this returns its package
     * specifier instead, with no change to Core.
     */
    public static function frontendSource(): string
    {
        return '@/Modules/Store';
    }
}
