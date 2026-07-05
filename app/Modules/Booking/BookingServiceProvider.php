<?php

namespace App\Modules\Booking;

use App\Core\Support\PenovaModule;
use Illuminate\Contracts\Foundation\CachesRoutes;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Booking — demo business module: a minimal bookings CRUD in the admin
 * panel plus a "bookings today" dashboard widget.
 *
 * Everything the panel shows for this module is declared right here
 * (routes, menu(), widgets()); Core discovers it solely through its
 * entry in config('penova.modules').
 */
class BookingServiceProvider extends ServiceProvider implements PenovaModule
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
    }

    /** @see PenovaModule — sidebar contribution. */
    public static function menu(): array
    {
        return [
            ['key' => 'booking', 'label' => 'رزروها', 'route' => 'booking.index', 'icon' => 'calendar', 'order' => 200, 'permission' => 'booking.view'],
        ];
    }

    /**
     * @see PenovaModule — dashboard contribution.
     *
     * Data contract: BookingsTodayCard.vue (resources/js/Modules/Booking/
     * Widgets) fetches GET /admin/bookings/today-count (route
     * "booking.today-count") on mount; the response is always
     * { count: number, yesterday_count: number } — yesterday_count
     * drives the trend indicator and is optional on the consumer side.
     * See BookingsTodayCountController.
     */
    public static function widgets(): array
    {
        return [
            // area 'booking': every widget this module ships lands under
            // the same dashboard heading (config penova.widgets.areas).
            ['key' => 'booking-today-count', 'type' => 'card', 'title' => 'رزروهای امروز', 'component' => 'Modules/Booking/Widgets/BookingsTodayCard', 'cols' => 1, 'order' => 200, 'area' => 'booking', 'permission' => 'booking.view'],
        ];
    }

    /**
     * @see PenovaModule — the module's permission manifest. The slugs
     * are created by BookingPermissionsSeeder; keep the three in sync
     * (manifest, seeder, route middleware).
     */
    public static function permissions(): array
    {
        return [
            'booking.view',
            'booking.manage',
        ];
    }
}
