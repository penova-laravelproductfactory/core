<?php

namespace Tests;

use App\Modules\Store\StoreServiceProvider;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Bootstrap\RegisterProviders;
use Illuminate\Foundation\Testing\RefreshDatabaseState;

/**
 * Base test case for the Store MODULE lane (tests under tests/Feature/Store).
 *
 * Since D-026, Core enables no business module by default — config
 * `penova.modules` ships empty. Store's own tests therefore explicitly
 * enable the Store module, exactly the application-level opt-in a real app
 * makes when it wires Store into `config/penova.php`. This keeps the Core
 * lane honest (it runs with no module) while Store integration tests get
 * Store's routes and migrations.
 *
 * The module list is read by PenovaCoreServiceProvider::register(), so it
 * must be set *before* providers register. `beforeBootstrapping(
 * RegisterProviders)` is the one hook that fires after configuration is
 * loaded but before providers register — so Store boots (routes +
 * loadMigrationsFrom) before RefreshDatabase migrates.
 */
abstract class StoreTestCase extends TestCase
{
    /**
     * RefreshDatabase migrates once per suite (a static flag). Core-lane
     * tests migrate WITHOUT Store, so its migrations never register. Force a
     * fresh migration for each Store test — now that Store is enabled (see
     * createApplication), its migrations are loaded and the schema includes
     * store_* tables.
     */
    protected function setUp(): void
    {
        RefreshDatabaseState::$migrated = false;

        parent::setUp();
    }

    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->beforeBootstrapping(
            RegisterProviders::class,
            fn ($app) => $app['config']->set('penova.modules', [StoreServiceProvider::class]),
        );

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
