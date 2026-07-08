<?php

namespace App\Modules\Store;

use App\Core\Support\Manifest;
use App\Core\Support\PenovaModule;
use Illuminate\Contracts\Foundation\CachesRoutes;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Store — minimal but real e-commerce module: product management
 * (physical / virtual / downloadable) plus an "active products"
 * dashboard widget. Orders, cart and checkout build on this skeleton
 * in later versions.
 *
 * Data contract: ActiveProductsCard.vue (resources/js/Modules/Store/
 * Widgets) fetches GET /admin/store/products/active-count (route
 * "store.products.active-count"); the response is always
 * { count: number }.
 *
 * The module declares everything it contributes through one Manifest
 * (see manifest()); routes and migrations are provider mechanics, wired
 * in boot() — they are not Manifest contributions (D-023).
 */
class StoreServiceProvider extends ServiceProvider implements PenovaModule
{
    public function boot(): void
    {
        // Module routes live in routes.php as plain definitions; the
        // panel group (URI prefix + auth middleware) is applied here.
        // Cache guard mirrors loadRoutesFrom() so route:cache stays safe.
        if (! ($this->app instanceof CachesRoutes && $this->app->routesAreCached())) {
            // Admin surface: /admin/store/... (web + auth + permissions).
            Route::middleware(config('penova.admin.middleware'))
                ->prefix(config('penova.admin.prefix'))
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
                ['key' => 'store-active-products', 'type' => 'card', 'title' => 'محصولات فعال', 'component' => 'Modules/Store/Widgets/ActiveProductsCard', 'cols' => 1, 'order' => 400, 'area' => 'store', 'permission' => 'store.view'],
            ])
            ->permissions(['store.view', 'store.manage']);
    }
}
