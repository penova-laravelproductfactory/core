<?php

namespace App\Core\Support\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Core\Support — `penova:module {name}`.
 *
 * Scaffolds a new product module following the PenovaModule contract,
 * with a WORKING Index/Create/Store CRUD out of the box: service
 * provider, permission-guarded routes, model + migration, form request,
 * three invokable controllers, a permissions seeder, and two Vue pages.
 *
 * Stubs live in stubs/penova/module/*.stub; placeholders (plain
 * str_replace, no Blade):
 *
 *   {{ name }}   → StudlyCase module name          (Reports)
 *   {{ module }} → kebab-case module key           (reports) — route
 *                  names, permission slugs, widget area
 *   {{ entity }} → singular StudlyCase model name  (Report)
 *   {{ table }}  → plural snake_case table name    (reports)
 *   {{ slug }}   → plural kebab-case URI segment   (reports)
 *
 * The module is NOT auto-registered: the command prints the
 * config/penova.php and DatabaseSeeder lines to add, keeping wiring
 * explicit and reviewable.
 */
class MakePenovaModuleCommand extends Command
{
    protected $signature = 'penova:module {name : The module name in StudlyCase, e.g. Booking}';

    protected $description = 'Scaffold a new Penova module (service provider, routes, basic CRUD skeleton).';

    public function handle(): int
    {
        $name = Str::studly($this->argument('name'));
        $entity = Str::studly(Str::singular($name));
        $base = app_path('Modules/'.$name);

        if (File::exists($base)) {
            $this->error("Module already exists: app/Modules/{$name}");

            return self::FAILURE;
        }

        $replacements = [
            '{{ name }}' => $name,
            '{{ module }}' => Str::kebab($name),
            '{{ entity }}' => $entity,
            '{{ table }}' => Str::plural(Str::snake($name)),
            '{{ slug }}' => Str::plural(Str::kebab($name)),
        ];

        // path (relative to the module/frontend root) => stub file
        $backend = [
            "{$name}ServiceProvider.php" => 'module.stub',
            'routes.php' => 'routes.stub',
            "Models/{$entity}.php" => 'model.stub',
            "Requests/Store{$entity}Request.php" => 'request.stub',
            "Controllers/{$name}IndexController.php" => 'index-controller.stub',
            "Controllers/{$name}CreateController.php" => 'create-controller.stub',
            "Controllers/{$name}StoreController.php" => 'store-controller.stub',
            'Database/Migrations/'.now()->format('Y_m_d_His')."_create_{$replacements['{{ table }}']}_table.php" => 'migration.stub',
            "Database/Seeders/{$name}PermissionsSeeder.php" => 'permissions-seeder.stub',
        ];

        $frontend = [
            'Pages/Index.vue' => 'page-index.stub',
            'Pages/Create.vue' => 'page-create.stub',
        ];

        $created = [];

        foreach ($backend as $path => $stub) {
            $created[] = $this->write("{$base}/{$path}", $stub, $replacements);
        }

        $frontendBase = resource_path("js/Modules/{$name}");

        foreach ($frontend as $path => $stub) {
            $created[] = $this->write("{$frontendBase}/{$path}", $stub, $replacements);
        }

        // Widgets folder stays empty until the module ships one.
        File::ensureDirectoryExists("{$frontendBase}/Widgets");
        File::put("{$frontendBase}/Widgets/.gitkeep", '');

        $this->info("Module scaffolded: app/Modules/{$name}");

        foreach ($created as $file) {
            $this->line('  created: '.str_replace('\\', '/', Str::after($file, base_path().DIRECTORY_SEPARATOR)));
        }

        $this->newLine();
        $this->comment('Next steps:');
        $this->line("  1. Register the provider in config/penova.php → 'modules':");
        $this->line("       App\\Modules\\{$name}\\{$name}ServiceProvider::class,");
        $this->line("  2. Register the seeder in database/seeders/DatabaseSeeder.php (after PenovaCoreSeeder):");
        $this->line("       \\App\\Modules\\{$name}\\Database\\Seeders\\{$name}PermissionsSeeder::class,");
        $this->line('  3. Run: php artisan migrate && php artisan db:seed');
        $this->line("  4. Replace the TODO labels (menu label, page copy) and grow the {$entity} domain.");
        $this->line('     Reference: app/Modules/README.md');

        return self::SUCCESS;
    }

    /** Render one stub to disk (creating parent folders) and return the path. */
    private function write(string $path, string $stub, array $replacements): string
    {
        File::ensureDirectoryExists(dirname($path));
        File::put($path, str_replace(
            array_keys($replacements),
            array_values($replacements),
            File::get(base_path("stubs/penova/module/{$stub}")),
        ));

        return $path;
    }
}
