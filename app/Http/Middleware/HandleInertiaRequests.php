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

        return [
            ...parent::share($request),

            'app' => [
                'name' => config('penova.name'),
            ],

            // Sidebar items (Core + Modules, order-sorted). Route names
            // are resolved to URLs here — at request time, when all module
            // routes are guaranteed to be registered.
            'menu' => collect(app('penova.menu'))->map(fn (array $item) => [
                ...$item,
                'href' => Route::has($item['route']) ? route($item['route'], absolute: false) : '#',
            ])->all(),

            // Dashboard widget descriptors (Core + Modules, order-sorted);
            // Core/Pages/Dashboard/Index.vue renders the grid from these.
            'dashboardWidgets' => app('penova.widgets'),

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
