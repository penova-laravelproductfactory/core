# app/Modules — Product Modules

Business-specific products (Booking, CRM, CMS, …) live here, one folder
per module. **Core never imports from this namespace** — modules build on
Core, not the other way around. If code is reusable across two modules,
it belongs in `app/Core`.

A working reference module ships in `app/Modules/Demo` — copy it to start
a new module.

## Anatomy of a module

```
app/Modules/Booking/
  BookingServiceProvider.php   ← the module's single entry point
  routes.php                   ← module routes (invokable controllers)
  Controllers/                 ← ONE invokable class per route action
  Models/
  Requests/
  Policies/
  Database/
    Migrations/                ← loadMigrationsFrom() in the provider
    Seeders/                   ← seeds the module's permissions ("booking.manage")
```

Frontend lives in `resources/js/Modules/Booking/`:

```
resources/js/Modules/Booking/
  Pages/       ← Inertia pages: Inertia::render('Modules/Booking/Calendar')
  Widgets/     ← dashboard widgets referenced by widget descriptors
```

Both resolve automatically — no frontend registration step.

## Wiring a module in

Add its provider to `config/penova.php` — the **only** place a module
touches shared configuration:

```php
'modules' => [
    App\Modules\Booking\BookingServiceProvider::class,
],
```

`PenovaCoreServiceProvider` registers it — Core stays free of any
compile-time reference to the module.

## The module contract (`App\Core\Support\PenovaModule`)

The provider implements `PenovaModule` (recommended — Core actually
discovers the hooks with `method_exists()`, so the interface is
documentation + type-safety, not a hard requirement):

```php
use App\Core\Support\PenovaModule;
use Illuminate\Support\ServiceProvider;

class BookingServiceProvider extends ServiceProvider implements PenovaModule
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
    }

    public static function menu(): array { /* see below */ }
    public static function widgets(): array { /* see below */ }
}
```

### Sidebar menu — `menu()`

Returns an array of items; Core merges them with its own (orders 10–60)
and sorts by `order`. Use `order >= 100` for module items.

```php
public static function menu(): array
{
    return [[
        'key'   => 'booking',        // unique across the panel
        'label' => 'رزروها',
        'route' => 'booking.index',  // route NAME — Core resolves the URL
        'icon'  => 'calendar',       // icon key; the map lives in AdminLayout.vue
                                     // (home|users|shield|cog|clock|bell|calendar|sparkles|squares)
        'order' => 100,
    ]];
}
```

### Dashboard widgets — `widgets()`

Returns widget **descriptors**; the dashboard grid renders them sorted by
`order`. Core's own widgets use orders 10–30 (and 900 for the Pro pitch),
so modules land in the middle with `order >= 100`.

```php
public static function widgets(): array
{
    return [[
        'key'       => 'booking-latest',
        'type'      => 'card',            // 'card' | 'list'
        'title'     => 'آخرین رزروها',    // arrives as widget.title in Vue
        'component' => 'Modules/Booking/Widgets/LatestBookings',
                        // → resources/js/Modules/Booking/Widgets/LatestBookings.vue
        'cols'      => 1,                 // 1 | 2 | 'full' (whole row, any grid width)
        'order'     => 100,
        'area'      => 'booking',         // optional dashboard group (see below)
    ]];
}
```

**Areas.** The dashboard renders one headed section per `area`, so a
module's widgets stay visually grouped. Recommended: give your module its
own area named after it (`'area' => 'booking'`) and reuse it on every
widget the module ships. Omitting `area` drops the widget into the
default `core` group. Section headings come from
`config('penova.widgets.areas')` — add your key there for a proper label;
unknown keys fall back to a label formatted from the key itself
(`booking-extras` → "Booking Extras").

The Vue widget receives the whole descriptor as the `widget` prop and
reads its data from Inertia page/shared props (Lite convention — see
`resources/js/Core/Widgets/*` for the pattern):

```vue
<script setup>
import Card from '@/Core/Components/Card.vue';
defineProps({ widget: Object });
</script>

<template>
    <Card :title="widget.title">…</Card>
</template>
```

## Routes: invokable controllers only

Every route action — Core and Modules alike — is **one invokable
controller class**. Naming convention: `{Verb}{Subject}Controller`, verbs
from this set:

| Verb    | Action                       | Example                    |
|---------|------------------------------|----------------------------|
| List    | index page                   | `ListBookingsController`   |
| Show    | single page / form display   | `ShowBookingController`    |
| Create  | "new X" form page            | `CreateBookingController`  |
| Store   | persist a new record         | `StoreBookingController`   |
| Edit    | "edit X" form page           | `EditBookingController`    |
| Update  | apply edits                  | `UpdateBookingController`  |
| Delete  | destroy                      | `DeleteBookingController`  |

Module routes reuse the panel middleware + URI prefix from config, but own
their route-name prefix (never `penova.*`):

```php
Route::middleware(config('penova.admin.middleware'))
    ->prefix(config('penova.admin.prefix'))
    ->as('booking.')
    ->group(function () {
        Route::get('bookings', ListBookingsController::class)->name('index');
    });
```

Guard module pages with the permission middleware and seed the permission
from the module's own seeder: `->middleware('permission:booking.manage')`.
