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
        // Index.vue): stats + the two feeds share one row, the Pro pitch
        // spans the full row via cols 'full'.
        ['key' => 'core-stats', 'type' => 'card', 'title' => 'آمار کلی', 'component' => 'Core/Widgets/UsersStats', 'cols' => 1, 'order' => 10],
        ['key' => 'core-recent-activity', 'type' => 'list', 'title' => 'فعالیت‌های اخیر', 'component' => 'Core/Widgets/RecentActivity', 'cols' => 1, 'order' => 20],
        ['key' => 'core-recent-notifications', 'type' => 'list', 'title' => 'اعلان‌های اخیر', 'component' => 'Core/Widgets/RecentNotifications', 'cols' => 1, 'order' => 30],
        ['key' => 'core-pro-pitch', 'type' => 'card', 'title' => 'ماژول‌ها و نسخه Pro', 'component' => 'Core/Widgets/ProPitch', 'cols' => 'full', 'order' => 900],
    ];

    public function register(): void
    {
        // In-app config/penova.php is auto-loaded by Laravel; this merge
        // only matters if Core is ever extracted into a package. Kept for
        // that future, and it is harmless today.
        $this->mergeConfigFrom(config_path('penova.php'), 'penova');

        // Core singletons — the abstractions Modules program against.
        $this->app->singleton(SettingsManager::class);

        // Installed-module manifest registry (Workspace + future tooling).
        $this->app->singleton(Support\ManifestRegistry::class);

        // Boot product modules. Core iterates class-strings only; it has
        // no compile-time dependency on anything in app/Modules.
        foreach (config('penova.modules', []) as $provider) {
            $this->app->register($provider);
        }

        // Panel composition: Core contributions + whatever each module's
        // provider declares through the Support\PenovaModule contract.
        // Lazy singletons — resolved once, on first use (Inertia share).
        $this->app->singleton('penova.menu', fn () => $this->collectFromModules('menu', self::CORE_MENU));

        // Widgets are normalised so 'area' is always present ('core' when
        // a descriptor omits it) — the dashboard groups by this field.
        $this->app->singleton('penova.widgets', fn () => array_map(
            fn (array $widget) => [...$widget, 'area' => $widget['area'] ?? 'core'],
            $this->collectFromModules('widgets', self::CORE_WIDGETS),
        ));

        // Flat list of every permission slug the modules declare
        // (their manifests). Not shared with the frontend — available
        // for sanity checks, artisan tooling, and future admin UI.
        $this->app->singleton('penova.permissions', fn () => collect($this->modulesImplementingContract())
            ->flatMap(fn (string $provider) => $provider::permissions())
            ->unique()
            ->values()
            ->all());
    }

    /**
     * Merge Core's own descriptors with those of every registered module
     * provider implementing the PenovaModule contract, sorted by 'order'.
     */
    private function collectFromModules(string $hook, array $core): array
    {
        return collect($core)
            ->concat(collect($this->modulesImplementingContract())
                ->flatMap(fn (string $provider) => $provider::$hook()))
            ->sortBy('order')
            ->values()
            ->all();
    }

    /**
     * Registered module providers that implement the formal contract.
     * Providers without it still boot (register() above) but contribute
     * nothing to the panel composition.
     *
     * @return list<class-string<Support\PenovaModule>>
     */
    private function modulesImplementingContract(): array
    {
        return array_values(array_filter(
            config('penova.modules', []),
            fn (string $provider) => is_subclass_of($provider, Support\PenovaModule::class),
        ));
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
