<?php

namespace App\Core\Support;

/**
 * The Penova module contract.
 *
 * A product module's service provider implements this interface to
 * declare what it contributes to the shared panel shell. Core discovers
 * modules ONLY through config('penova.modules') class-strings — it calls
 * these statics via method_exists(), so implementing the interface is
 * recommended (self-documenting, IDE-friendly) but not enforced.
 *
 * Menu item shape (all keys required unless noted):
 *
 *   [
 *       'key'   => 'booking',         // unique across the whole panel
 *       'label' => 'رزروها',          // sidebar text (fa)
 *       'route' => 'booking.index',   // Laravel route NAME; Core resolves
 *                                     // it to a URL when sharing props
 *       'icon'  => 'calendar',        // icon key mapped in AdminLayout.vue
 *                                     // (home|users|shield|cog|clock|bell|
 *                                     //  calendar|sparkles|squares — extend
 *                                     //  the map there for new keys)
 *       'order' => 50,                // sidebar sort position; Core items
 *                                     // use 10..60, modules usually 100+
 *   ]
 *
 * Widget descriptor shape:
 *
 *   [
 *       'key'       => 'booking-latest',        // unique
 *       'type'      => 'card',                  // 'card' | 'list' (informational for now)
 *       'title'     => 'آخرین رزروها',          // passed to the Vue widget
 *       'component' => 'Modules/Booking/Widgets/LatestBookings',
 *                       // Vue file, resolved from resources/js/:
 *                       //   Core/Widgets/X    → resources/js/Core/Widgets/X.vue
 *                       //   Modules/N/Widgets/X → resources/js/Modules/N/Widgets/X.vue
 *       'cols'      => 1,                       // 1 | 2 | 'full' — grid cells the widget
 *                                               // spans; 'full' takes the whole row
 *                                               // regardless of the area's column count
 *       'order'     => 100,                     // dashboard sort position
 *       'area'      => 'booking',               // optional logical group; the dashboard
 *                                               // renders one headed section per area.
 *                                               // Defaults to 'core' when omitted.
 *                                               // Recommendation: one area per module,
 *                                               // named after it. Headings come from
 *                                               // config('penova.widgets.areas'); unknown
 *                                               // keys get a label formatted from the key.
 *   ]
 */
interface PenovaModule
{
    /**
     * Sidebar menu items this module contributes.
     *
     * @return array<int, array{key: string, label: string, route: string, icon: string, order: int}>
     */
    public static function menu(): array;

    /**
     * Dashboard widget descriptors this module contributes.
     *
     * @return array<int, array{key: string, type: string, title: string, component: string, cols: int|string, order: int, area?: string}>
     */
    public static function widgets(): array;
}
