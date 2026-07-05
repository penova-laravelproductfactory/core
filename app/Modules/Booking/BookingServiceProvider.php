<?php

namespace App\Modules\Booking;

use App\Core\Support\PenovaModule;
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
        if (! $this->app->routesAreCached()) {
            Route::middleware(config('penova.admin.middleware'))
                ->prefix(config('penova.admin.prefix'))
                ->group(__DIR__.'/routes.php');
        }
    }

    /** @see PenovaModule — sidebar contribution. */
    public static function menu(): array
    {
        return [
            ['key' => 'booking', 'label' => 'رزروها', 'route' => 'booking.index', 'icon' => 'calendar', 'order' => 200],
        ];
    }

    /**
     * @see PenovaModule — dashboard contribution.
     *
     * Frontend note (next phase): create
     * resources/js/Modules/Booking/Widgets/BookingsTodayCard.vue —
     * receive the descriptor as the `widget` prop, fetch
     * GET /admin/bookings/today-count (route "booking.today-count",
     * returns {count}) on mount, and render the number in a Card/
     * StatsCard. Until that file exists the dashboard shows the
     * graceful "widget not found" placeholder.
     */
    public static function widgets(): array
    {
        return [
            ['key' => 'booking-today-count', 'type' => 'card', 'title' => 'رزروهای امروز', 'component' => 'Modules/Booking/Widgets/BookingsTodayCard', 'cols' => 1, 'order' => 200],
        ];
    }
}
