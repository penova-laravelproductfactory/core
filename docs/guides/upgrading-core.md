# Upgrading Core

Migration guidance for breaking changes between MAJOR versions of Penova Core.
Each section is a task: what changed, and the path forward.

---

## Store is no longer enabled by default (RFC-004 / D-026)

**Default changed.** Core enables no business module by default — the
`config/penova.php` `modules` array now ships **empty**, Core's `DatabaseSeeder`
seeds only Core, and Core's login/welcome pages carry no Store-specific content.
Core is complete on its own; commerce is a Module you opt into.

- **Impact.** An application that relied on Store being present by default must
  now **explicitly enable the Store module** and include its seeding/composition
  step. A fresh install no longer has Store's routes, menu, widgets, or
  permissions until Store is enabled.
- **Why MAJOR.** The configuration *format* is unchanged, but the shipped
  *default behaviour* changes in a way an upgrader can observe — so it lands in a
  MAJOR release with this migration note.
- **Recovery path.** Re-enable Store explicitly:
  1. Add its provider to `config/penova.php`:
     ```php
     'modules' => [
         App\Modules\Store\StoreServiceProvider::class,
     ],
     ```
  2. Compose its seeding at the application layer — add
     `App\Modules\Store\Database\Seeders\StorePermissionsSeeder` to your
     application's seeding (Core no longer names it).
  3. Run the module's seeding path (e.g. `php artisan db:seed`).

  No code was removed — Store still ships in the repository — so recovery is a
  configuration/composition change only.
