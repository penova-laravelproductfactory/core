<?php

namespace App\Core\Support\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Core\Support — `penova:module {name}`.
 *
 * Scaffolds a new product module with the Booking/Crm anatomy: a
 * PenovaModule-implementing service provider, a plain routes.php, the
 * backend folders, and the frontend Pages/Widgets folders. Stubs live
 * in stubs/penova/module/*.stub; placeholders:
 *
 *   {{ name }}   → StudlyCase module name  (e.g. "Reports")
 *   {{ module }} → kebab-case module key   (e.g. "reports") — used for
 *                  route names, permission slugs, and the widget area
 *
 * The module is NOT auto-registered: the command prints the
 * config/penova.php line to add, keeping wiring explicit and reviewable.
 */
class MakePenovaModuleCommand extends Command
{
    protected $signature = 'penova:module {name : The module name in StudlyCase, e.g. Booking}';

    protected $description = 'Scaffold a new Penova module (service provider, structure, basic files).';

    public function handle(): int
    {
        $name = Str::studly($this->argument('name'));
        $module = Str::kebab($name);
        $base = app_path('Modules/'.$name);

        if (File::exists($base)) {
            $this->error("Module already exists: app/Modules/{$name}");

            return self::FAILURE;
        }

        // Backend skeleton (empty folders carry .gitkeep so git tracks them).
        foreach (['Models', 'Controllers', 'Requests', 'Database/Seeders'] as $dir) {
            File::ensureDirectoryExists("{$base}/{$dir}");
            File::put("{$base}/{$dir}/.gitkeep", '');
        }

        // Frontend skeleton — matches the paths the widget/page resolvers
        // expect (resources/js/Modules/<Name>/Pages|Widgets).
        $frontend = resource_path("js/Modules/{$name}");
        foreach (['Pages', 'Widgets'] as $dir) {
            File::ensureDirectoryExists("{$frontend}/{$dir}");
            File::put("{$frontend}/{$dir}/.gitkeep", '');
        }

        File::put("{$base}/{$name}ServiceProvider.php", $this->renderStub('module.stub', $name, $module));
        File::put("{$base}/routes.php", $this->renderStub('routes.stub', $name, $module));

        $this->info("Module scaffolded: app/Modules/{$name}");
        $this->line("  backend:  app/Modules/{$name}/{{$name}ServiceProvider.php, routes.php, Models/, Controllers/, Requests/, Database/Seeders/}");
        $this->line("  frontend: resources/js/Modules/{$name}/{Pages/, Widgets/}");
        $this->newLine();
        $this->comment('Next steps:');
        $this->line("  1. Register it in config/penova.php → 'modules':");
        $this->line("       App\\Modules\\{$name}\\{$name}ServiceProvider::class,");
        $this->line("  2. Fill in menu()/widgets()/permissions() in the provider.");
        $this->line("  3. Follow app/Modules/README.md for routes, controllers, and the permissions seeder.");

        return self::SUCCESS;
    }

    private function renderStub(string $stub, string $name, string $module): string
    {
        return str_replace(
            ['{{ name }}', '{{ module }}'],
            [$name, $module],
            File::get(base_path("stubs/penova/module/{$stub}")),
        );
    }
}
