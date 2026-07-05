<?php

namespace App\Core\Support;

/**
 * The Penova module contract.
 *
 * A product module's service provider MUST implement this interface to
 * contribute to the shared panel shell. Core discovers modules ONLY
 * through config('penova.modules') class-strings and collects the
 * static hooks below from providers that implement this interface
 * (is_subclass_of check in PenovaCoreServiceProvider) — a provider
 * without it still boots, but contributes nothing to menu/widgets.
 * Hooks a module does not need simply return [].
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
 *       'permission' => 'booking.view', // optional; item is only shown to
 *                                     // users holding this permission slug
 *                                     // (filtered per-request by
 *                                     // HandleInertiaRequests). Should
 *                                     // mirror the route's middleware.
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
 *       'permission' => 'booking.view',         // optional; same semantics as the menu
 *                                               // field — the widget is dropped from
 *                                               // dashboardWidgets for users without it
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
     * @return array<int, array{key: string, label: string, route: string, icon: string, order: int, permission?: string}>
     */
    public static function menu(): array;

    /**
     * Dashboard widget descriptors this module contributes.
     *
     * @return array<int, array{key: string, type: string, title: string, component: string, cols: int|string, order: int, area?: string, permission?: string}>
     */
    public static function widgets(): array;

    /**
     * Permission slugs this module defines (e.g. ['booking.view',
     * 'booking.manage']). Declarative for now: the slugs are CREATED by
     * the module's permissions seeder — this list is the module's own
     * manifest of them, collected into the 'penova.permissions' binding
     * for documentation, sanity checks, and future admin UI. Keep it in
     * sync with the seeder and the route middleware.
     *
     * @return list<string>
     */
    public static function permissions(): array;
}
