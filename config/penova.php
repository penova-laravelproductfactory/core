<?php

/*
|--------------------------------------------------------------------------
| Penova Core Configuration
|--------------------------------------------------------------------------
|
| Central configuration for the Penova product factory core. Products
| built on top of Penova (Store, CMS, ...) override these values
| in their own .env / config, never by editing Core code.
|
*/

return [

    // Human-readable product name, used in layouts and page titles.
    'name' => env('PENOVA_NAME', 'Penova'),

    // Platform version, shown in the Workspace hero and What's New.
    'version' => env('PENOVA_VERSION', '0.1.0'),

    /*
    |--------------------------------------------------------------------------
    | Platform Links & Changelog
    |--------------------------------------------------------------------------
    | External links surfaced in the Workspace, and a latest-first changelog
    | the "What's New" card reads (entries[0]). Future changelog automation
    | reuses this shape.
    */
    'links' => [
        'documentation' => env('PENOVA_DOCS_URL', 'https://github.com/penova-laravelproductfactory/core'),
        'github' => env('PENOVA_GITHUB_URL', 'https://github.com/penova-laravelproductfactory/core'),
        'release_notes' => env('PENOVA_RELEASES_URL', 'https://github.com/penova-laravelproductfactory/core/releases'),
    ],

    'changelog' => [
        [
            'version' => '0.1.0',
            'date' => '2026-07-06',
            'highlights' => [
                'White Label / Branding in Settings',
                'Onboarding Workspace',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Branding / White Label
    |--------------------------------------------------------------------------
    | Deploy-time defaults for the White Label surface. Admins override these
    | at runtime from Settings (stored under the single "branding" setting
    | key); the resolved values are shared with every Inertia page. Empty
    | runtime values fall back to these defaults.
    */
    'branding' => [
        'name' => env('PENOVA_BRAND_NAME', 'Penova Core'),
        'logo_url' => env('PENOVA_BRAND_LOGO'),
        'primary_color' => env('PENOVA_BRAND_PRIMARY_COLOR', '#01696f'),
        'footer_text' => env('PENOVA_BRAND_FOOTER', 'Powered by Penova'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Workspace
    |--------------------------------------------------------------------------
    | All Core Workspace routes (users, roles, settings, logs, ...) are grouped
    | under this URI prefix and the "penova." route-name prefix.
    |
    | The legacy PENOVA_ADMIN_PREFIX env var is honoured for one MAJOR line
    | (new key first, legacy second); PenovaCoreServiceProvider emits a
    | deprecation notice when a legacy PENOVA_ADMIN_* value answers. See
    | RFC-002 / D-024.
    */
    'workspace' => [
        'prefix' => env('PENOVA_WORKSPACE_PREFIX', env('PENOVA_ADMIN_PREFIX', 'workspace')),
        'middleware' => ['web', 'auth'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Seed Operator
    |--------------------------------------------------------------------------
    | Credentials for the initial seeded Operator (used only by
    | PenovaCoreSeeder). Dev/test convenience — override via env in any real
    | environment and rotate after first login (builder-owned; D-014 / 15).
    */
    'operator' => [
        'email' => env('PENOVA_OPERATOR_EMAIL', env('PENOVA_ADMIN_EMAIL', 'operator@example.com')),
        'password' => env('PENOVA_OPERATOR_PASSWORD', env('PENOVA_ADMIN_PASSWORD', 'password')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    | Registration is optional per product: an internal admin tool turns
    | it off, a public storefront turns it on.
    */
    'auth' => [
        // Core default: self-registration OFF. Products that need
        // public signup set PENOVA_REGISTRATION=true in their .env.
        'registration' => (bool) env('PENOVA_REGISTRATION', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | DataTable Defaults
    |--------------------------------------------------------------------------
    | Shared defaults for the Core\DataTable infrastructure (server-side
    | pagination / sorting / filtering). Individual tables may override.
    */
    'datatable' => [
        'per_page' => 15,
        'max_per_page' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Activity Logging
    |--------------------------------------------------------------------------
    */
    'logs' => [
        'enabled' => env('PENOVA_ACTIVITY_LOG', true),
        // Days to keep activity logs before pruning (null = keep forever).
        'retention_days' => env('PENOVA_LOG_RETENTION', 90),
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Widgets
    |--------------------------------------------------------------------------
    | 'areas' maps a widget area key to the heading the dashboard shows
    | above that group. Modules are free to introduce new area keys (the
    | recommendation is one area per module, named after it); a key
    | missing from this map falls back to a label formatted from the key
    | itself ("store-extras" → "Store Extras") on the frontend.
    */
    'widgets' => [
        'areas' => [
            'core' => 'عمومی',
            // Module area headings are NOT declared here — Core carries no
            // Module-specific vocabulary. A key missing from this map falls
            // back to a label formatted from the key on the frontend.
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Product Modules
    |--------------------------------------------------------------------------
    | Business modules living in app/Modules. Each entry points to the
    | module's service provider; Core boots them but never depends on them.
    | A provider implementing the PenovaModule contract (see
    | app/Core/Support/PenovaModule.php) declares what it contributes —
    | menu, widgets, permissions — through one Manifest. This list is the
    | ONLY place modules get wired in.
    |
    | 'modules' => [
    |     App\Modules\Store\StoreServiceProvider::class,
    | ],
    */
    'modules' => [
        // Store module: product management (physical/virtual/downloadable)
        // + "active products" widget. Orders/cart come later.
        App\Modules\Store\StoreServiceProvider::class,
    ],

];
