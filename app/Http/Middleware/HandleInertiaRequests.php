<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Middleware;

/**
 * Shares global props with every Inertia page (Core and Module pages
 * alike): the authenticated user, flash messages, unread notification
 * count for the layout bell, the product name, and the panel composition
 * (sidebar menu + dashboard widgets) collected from Core + Modules by
 * PenovaCoreServiceProvider.
 */
class HandleInertiaRequests extends Middleware
{
    /** The root template loaded on first page visit. */
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $user = $request->user();

        // Menu items / widgets carrying a 'permission' key are only
        // shared with users holding it (per-request — the collected
        // descriptors themselves are app-wide singletons).
        $allowed = fn (array $item) => ! isset($item['permission'])
            || ($user?->hasPermission($item['permission']) ?? false);

        return [
            ...parent::share($request),

            'app' => [
                'name' => config('penova.name'),
            ],

            // Sidebar items (Core + Modules, order-sorted, permission-
            // filtered). Route names are resolved to URLs here — at
            // request time, when all module routes are registered.
            'menu' => collect(app('penova.menu'))
                ->filter($allowed)
                ->map(fn (array $item) => [
                    ...$item,
                    'href' => Route::has($item['route']) ? route($item['route'], absolute: false) : '#',
                ])
                ->values()
                ->all(),

            // Dashboard widget descriptors (Core + Modules, order-sorted,
            // permission-filtered); Core/Pages/Dashboard/Index.vue renders
            // the grid from these.
            'dashboardWidgets' => collect(app('penova.widgets'))
                ->filter($allowed)
                ->values()
                ->all(),

            // Widget area → heading map for the dashboard sections; keys
            // missing here get a label formatted from the key itself.
            'widgetAreas' => config('penova.widgets.areas', []),

            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('slug'),
                ] : null,
            ],

            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],

            'unreadNotifications' => $user
                ? $user->unreadNotifications()->count()
                : 0,
        ];
    }
}
