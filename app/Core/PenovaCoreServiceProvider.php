<?php

namespace App\Core;

use App\Core\Roles\Middleware\EnsureUserHasPermission;
use App\Core\Roles\Models\Role;
use App\Core\Roles\Policies\RolePolicy;
use App\Core\Settings\Services\SettingsManager;
use App\Core\Users\Models\User;
use App\Core\Users\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Penova Core — central service provider.
 *
 * Everything the Core needs to run is registered here: config, routes,
 * middleware aliases, policies, singletons, and the product Modules
 * listed in config('penova.modules').
 *
 * ARCHITECTURE RULES (enforced by convention — keep them true):
 *   1. app/Core NEVER imports from app/Modules. Core knows modules only
 *      as opaque provider class-strings in config/penova.php.
 *   2. Modules build ON Core: they may use Core models, services,
 *      middleware and Vue components freely.
 *   3. Code needed by two or more modules is Core code — move it here.
 */
class PenovaCoreServiceProvider extends ServiceProvider
{
    /**
     * Sidebar items Core itself ships. Modules append theirs through their
     * Manifest (see Support\PenovaModule); everything is merged and
     * order-sorted in the 'penova.menu' binding below.
     */
    private const CORE_MENU = [
        // 'permission' mirrors each route's middleware guard: items the
        // user could only 403 on are filtered out of the sidebar
        // (per-request, in HandleInertiaRequests). Workspace and
        // notifications are open to every authenticated panel user.
        ['key' => 'workspace', 'label' => 'میزکار', 'route' => 'penova.workspace', 'icon' => 'home', 'order' => 10],
        ['key' => 'users', 'label' => 'کاربران', 'route' => 'penova.users.index', 'icon' => 'users', 'order' => 20, 'permission' => 'users.manage'],
        ['key' => 'roles', 'label' => 'نقش‌ها و دسترسی‌ها', 'route' => 'penova.roles.index', 'icon' => 'shield', 'order' => 30, 'permission' => 'roles.manage'],
        ['key' => 'settings', 'label' => 'تنظیمات', 'route' => 'penova.settings.index', 'icon' => 'cog', 'order' => 40, 'permission' => 'settings.manage'],
        ['key' => 'logs', 'label' => 'گزارش فعالیت‌ها', 'route' => 'penova.logs.index', 'icon' => 'clock', 'order' => 50, 'permission' => 'logs.view'],
        ['key' => 'notifications', 'label' => 'اعلان‌ها', 'route' => 'penova.notifications.index', 'icon' => 'bell', 'order' => 60],
    ];

    /**
     * Dashboard widgets Core itself ships — the dashboard is built
     * entirely from these descriptors, through the exact pipeline module
     * widgets use, so the pattern devs copy is the one Core runs.
     */
    private const CORE_WIDGETS = [
        // The 'core' area renders as a 3-column grid (see Dashboard
        // Index.vue): stats + the two feeds share one row, the Modules
        // card spans the full row via cols 'full'.
        ['key' => 'core-stats', 'type' => 'card', 'title' => 'آمار کلی', 'component' => 'Core/Widgets/UsersStats', 'cols' => 1, 'order' => 10],
        ['key' => 'core-recent-activity', 'type' => 'list', 'title' => 'فعالیت‌های اخیر', 'component' => 'Core/Widgets/RecentActivity', 'cols' => 1, 'order' => 20],
        ['key' => 'core-recent-notifications', 'type' => 'list', 'title' => 'اعلان‌های اخیر', 'component' => 'Core/Widgets/RecentNotifications', 'cols' => 1, 'order' => 30],
        ['key' => 'core-modules-card', 'type' => 'card', 'title' => 'ماژول‌های Penova', 'component' => 'Core/Widgets/ModulesCard', 'cols' => 'full', 'order' => 900],
    ];

    public function register(): void
    {
        // In-app config/penova.php is auto-loaded by Laravel; this merge
        // only matters if Core is ever extracted into a package. Kept for
        // that future, and it is harmless today.
        $this->mergeConfigFrom(config_path('penova.php'), 'penova');

        // Core singletons — the abstractions Modules program against.
        $this->app->singleton(SettingsManager::class);

        // The installed Modules' Manifest registry — the single source of
        // truth for everything Modules contribute (identities, menu items,
        // widget descriptors, permission slugs). The three panel-composition
        // bindings below all derive from it, so the module list is resolved
        // exactly once (D-023).
        $this->app->singleton(Support\ManifestRegistry::class);

        // Boot product modules. Core iterates class-strings only; it has
        // no compile-time dependency on anything in app/Modules.
        foreach (config('penova.modules', []) as $provider) {
            $this->app->register($provider);
        }

        // Panel composition: Core's own contributions + whatever each Module
        // declares through its Manifest, order-sorted. Lazy singletons —
        // resolved once, on first use (Inertia share).
        $this->app->singleton('penova.menu', fn () => collect(self::CORE_MENU)
            ->concat(app(Support\ManifestRegistry::class)->menuItems())
            ->sortBy('order')
            ->values()
            ->all());

        // Widgets are normalised so 'area' is always present ('core' when a
        // descriptor omits it) — the dashboard groups by this field.
        $this->app->singleton('penova.widgets', fn () => collect(self::CORE_WIDGETS)
            ->concat(app(Support\ManifestRegistry::class)->widgetDescriptors())
            ->map(fn (array $widget) => [...$widget, 'area' => $widget['area'] ?? 'core'])
            ->sortBy('order')
            ->values()
            ->all());

        // Flat list of every permission slug the Modules declare (from their
        // Manifests). Not shared with the frontend — available for sanity
        // checks, artisan tooling, and future admin UI.
        $this->app->singleton('penova.permissions', fn () => app(Support\ManifestRegistry::class)->permissionSlugs());
    }

    public function boot(): void
    {
        $this->registerMiddlewareAliases();
        $this->registerPolicies();
        $this->registerRoutes();

        if ($this->app->runningInConsole()) {
            $this->commands([
                Support\Commands\PenovaInstallCommand::class,
                Support\Commands\MakePenovaModuleCommand::class,
            ]);
        }

        // Blade views stay in resources/views (native location); the
        // "penova::" namespace exists so a future package build keeps
        // working without view changes.
        $this->loadViewsFrom(resource_path('views'), 'penova');
    }

    private function registerMiddlewareAliases(): void
    {
        Route::aliasMiddleware('permission', EnsureUserHasPermission::class);
    }

    private function registerPolicies(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
    }

    private function registerRoutes(): void
    {
        Route::middleware('web')->group(base_path('routes/penova.php'));
    }
}
