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
        'name' => env('PENOVA_BRAND_NAME', 'Penova Core Lite'),
        'logo_url' => env('PENOVA_BRAND_LOGO'),
        'primary_color' => env('PENOVA_BRAND_PRIMARY_COLOR', '#01696f'),
        'footer_text' => env('PENOVA_BRAND_FOOTER', 'Powered by Penova'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel
    |--------------------------------------------------------------------------
    | All Core panel routes (users, roles, settings, logs, ...) are grouped
    | under this URI prefix and route-name prefix ("penova.").
    */
    'admin' => [
        'prefix' => env('PENOVA_ADMIN_PREFIX', 'admin'),
        'middleware' => ['web', 'auth'],

        // Seed credentials for the initial admin account (used only by
        // PenovaCoreSeeder). Dev/test convenience — override via env in
        // any real environment and rotate after first login.
        'email' => env('PENOVA_ADMIN_EMAIL', 'admin@example.com'),
        'password' => env('PENOVA_ADMIN_PASSWORD', 'password'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    | Registration is optional per product: an internal admin tool turns
    | it off, a public storefront turns it on.
    */
    'auth' => [
        // Core Lite default: self-registration OFF. Products that need
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
            'store' => 'فروشگاه',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Product Modules
    |--------------------------------------------------------------------------
    | Business modules living in app/Modules. Each entry points to the
    | module's service provider; Core boots them but never depends on them.
    | A provider may also expose static menu() / widgets() hooks (see
    | app/Core/Support/PenovaModule.php) to contribute sidebar items and
    | dashboard widgets — this list is the ONLY place modules get wired in.
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
