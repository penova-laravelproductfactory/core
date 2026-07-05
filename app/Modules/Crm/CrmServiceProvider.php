<?php

namespace App\Modules\Crm;

use App\Core\Support\PenovaModule;
use Illuminate\Contracts\Foundation\CachesRoutes;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Crm — a deliberately light module (leads list + create + one dashboard
 * widget). Its main job is stress-testing the module architecture: a
 * third provider registering menu items and an own widget area proves
 * the composition pipeline scales past Booking.
 *
 * Data contract: LeadsTodayCard.vue (resources/js/Modules/Crm/Widgets)
 * fetches GET /admin/leads/today-count (route "crm.leads.today-count");
 * the response is always { count: number }.
 */
class CrmServiceProvider extends ServiceProvider implements PenovaModule
{
    public function boot(): void
    {
        // Same pattern as BookingServiceProvider: plain routes.php,
        // panel group applied here, cache guard framework-style.
        if (! ($this->app instanceof CachesRoutes && $this->app->routesAreCached())) {
            Route::middleware(config('penova.admin.middleware'))
                ->prefix(config('penova.admin.prefix'))
                ->group(__DIR__.'/routes.php');
        }
    }

    /** @see PenovaModule — sidebar contribution. */
    public static function menu(): array
    {
        return [
            ['key' => 'crm-leads', 'label' => 'سرنخ‌ها', 'route' => 'crm.leads.index', 'icon' => 'users', 'order' => 300, 'permission' => 'crm.leads.view'],
        ];
    }

    /** @see PenovaModule — dashboard contribution (own 'crm' area). */
    public static function widgets(): array
    {
        return [
            ['key' => 'crm-leads-today-count', 'type' => 'card', 'title' => 'سرنخ‌های امروز', 'component' => 'Modules/Crm/Widgets/LeadsTodayCard', 'cols' => 1, 'order' => 300, 'area' => 'crm', 'permission' => 'crm.leads.view'],
        ];
    }

    /**
     * @see PenovaModule — the module's permission manifest. The slugs
     * are created by CrmPermissionsSeeder; keep the three in sync
     * (manifest, seeder, route middleware).
     */
    public static function permissions(): array
    {
        return [
            'crm.leads.view',
            'crm.leads.manage',
        ];
    }
}
