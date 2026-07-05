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
     * Sidebar items Core itself ships. Modules append theirs via the
     * static menu() hook (see Support\PenovaModule); everything is merged
     * and order-sorted in the 'penova.menu' binding below.
     */
    private const CORE_MENU = [
        ['key' => 'dashboard', 'label' => 'داشبورد', 'route' => 'penova.dashboard', 'icon' => 'home', 'order' => 10],
        ['key' => 'users', 'label' => 'کاربران', 'route' => 'penova.users.index', 'icon' => 'users', 'order' => 20],
        ['key' => 'roles', 'label' => 'نقش‌ها و دسترسی‌ها', 'route' => 'penova.roles.index', 'icon' => 'shield', 'order' => 30],
        ['key' => 'settings', 'label' => 'تنظیمات', 'route' => 'penova.settings.index', 'icon' => 'cog', 'order' => 40],
        ['key' => 'logs', 'label' => 'گزارش فعالیت‌ها', 'route' => 'penova.logs.index', 'icon' => 'clock', 'order' => 50],
        ['key' => 'notifications', 'label' => 'اعلان‌ها', 'route' => 'penova.notifications.index', 'icon' => 'bell', 'order' => 60],
    ];

    /**
     * Dashboard widgets Core itself ships — the Lite dashboard is built
     * entirely from these descriptors, through the exact pipeline module
     * widgets use, so the pattern devs copy is the one Core runs.
     */
    private const CORE_WIDGETS = [
        ['key' => 'core-stats', 'type' => 'card', 'title' => 'آمار کلی', 'component' => 'Core/Widgets/UsersStats', 'cols' => 2, 'order' => 10],
        ['key' => 'core-recent-activity', 'type' => 'list', 'title' => 'فعالیت‌های اخیر', 'component' => 'Core/Widgets/RecentActivity', 'cols' => 1, 'order' => 20],
        ['key' => 'core-recent-notifications', 'type' => 'list', 'title' => 'اعلان‌های اخیر', 'component' => 'Core/Widgets/RecentNotifications', 'cols' => 1, 'order' => 30],
        ['key' => 'core-pro-pitch', 'type' => 'card', 'title' => 'ماژول‌ها و نسخه Pro', 'component' => 'Core/Widgets/ProPitch', 'cols' => 2, 'order' => 900],
    ];

    public function register(): void
    {
        // In-app config/penova.php is auto-loaded by Laravel; this merge
        // only matters if Core is ever extracted into a package. Kept for
        // that future, and it is harmless today.
        $this->mergeConfigFrom(config_path('penova.php'), 'penova');

        // Core singletons — the abstractions Modules program against.
        $this->app->singleton(SettingsManager::class);

        // Boot product modules. Core iterates class-strings only; it has
        // no compile-time dependency on anything in app/Modules.
        foreach (config('penova.modules', []) as $provider) {
            $this->app->register($provider);
        }

        // Panel composition: Core contributions + whatever each module's
        // provider declares through its static menu()/widgets() hooks.
        // Lazy singletons — resolved once, on first use (Inertia share).
        $this->app->singleton('penova.menu', fn () => $this->collectFromModules('menu', self::CORE_MENU));

        // Widgets are normalised so 'area' is always present ('core' when
        // a descriptor omits it) — the dashboard groups by this field.
        $this->app->singleton('penova.widgets', fn () => array_map(
            fn (array $widget) => [...$widget, 'area' => $widget['area'] ?? 'core'],
            $this->collectFromModules('widgets', self::CORE_WIDGETS),
        ));
    }

    /**
     * Merge Core's own descriptors with those of every registered module
     * provider exposing the given static hook, sorted by 'order'.
     *
     * method_exists() keeps the contract duck-typed: implementing
     * Support\PenovaModule is encouraged, not required.
     */
    private function collectFromModules(string $hook, array $core): array
    {
        $items = collect($core);

        foreach (config('penova.modules', []) as $provider) {
            if (method_exists($provider, $hook)) {
                $items = $items->concat($provider::$hook());
            }
        }

        return $items->sortBy('order')->values()->all();
    }

    public function boot(): void
    {
        $this->registerMiddlewareAliases();
        $this->registerPolicies();
        $this->registerRoutes();

        if ($this->app->runningInConsole()) {
            $this->commands([
                Support\Commands\PenovaInstallCommand::class,
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
