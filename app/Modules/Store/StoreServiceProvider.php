<?php

namespace App\Modules\Store;

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
 */
class StoreServiceProvider extends ServiceProvider implements PenovaModule
{
    public function boot(): void
    {
        // Module routes live in routes.php as plain definitions; the
        // panel group (URI prefix + auth middleware) is applied here.
        // Cache guard mirrors loadRoutesFrom() so route:cache stays safe.
        if (! ($this->app instanceof CachesRoutes && $this->app->routesAreCached())) {
            Route::middleware(config('penova.admin.middleware'))
                ->prefix(config('penova.admin.prefix'))
                ->group(__DIR__.'/routes.php');
        }

        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
    }

    /** @see PenovaModule — sidebar contribution. */
    public static function menu(): array
    {
        return [
            ['key' => 'store', 'label' => 'فروشگاه', 'route' => 'store.products.index', 'icon' => 'bag', 'order' => 400, 'permission' => 'store.view'],
        ];
    }

    /** @see PenovaModule — dashboard contribution (own 'store' area). */
    public static function widgets(): array
    {
        return [
            ['key' => 'store-active-products', 'type' => 'card', 'title' => 'محصولات فعال', 'component' => 'Modules/Store/Widgets/ActiveProductsCard', 'cols' => 1, 'order' => 400, 'area' => 'store', 'permission' => 'store.view'],
        ];
    }

    /**
     * @see PenovaModule — the module's permission manifest. The slugs
     * are created by StorePermissionsSeeder; keep the three in sync
     * (manifest, seeder, route middleware).
     */
    public static function permissions(): array
    {
        return [
            'store.view',
            'store.manage',
        ];
    }
}
