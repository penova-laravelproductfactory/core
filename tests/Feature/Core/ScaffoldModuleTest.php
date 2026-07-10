<?php

use App\Core\Support\Commands\GenerateFrontendRegistryCommand;
use App\Core\Support\ManifestRegistry;
use Illuminate\Support\Facades\File;

/**
 * Coherence — the `penova:module` scaffolder must emit a Module that WORKS under
 * the experimental frontend seam (RFC-006 / D-028): every page it renders is a
 * declared frontend entry, and those entries resolve through the Module's OWN
 * StudlyCase coordinate even though the Module key is kebab-case. Guards against
 * the generator silently drifting back to a broken/unregistered frontend.
 *
 * 'ReportHub' → key 'report-hub' (kebab), directory 'ReportHub' (Studly),
 * slug 'report-hubs' — the case the module-owned coordinate exists to solve.
 */
function cleanScaffold(): void
{
    File::deleteDirectory(app_path('Modules/ReportHub'));
    File::deleteDirectory(resource_path('js/Modules/ReportHub'));
    @unlink(GenerateFrontendRegistryCommand::path());
}

beforeEach(fn () => cleanScaffold());
afterEach(fn () => cleanScaffold());

test('a scaffolded Module resolves both pages through its StudlyCase coordinate and links Create to its route', function () {
    $this->artisan('penova:module', ['name' => 'ReportHub'])->assertSuccessful();

    // Enable the freshly scaffolded Module and rebuild the registry from it.
    config(['penova.modules' => ['App\\Modules\\ReportHub\\ReportHubServiceProvider']]);
    app()->forgetInstance(ManifestRegistry::class);

    // Generates AND passes --check — every declared entry resolves to a real file
    // under the Studly coordinate, so the scaffolded Module is not broken.
    $this->artisan('penova:frontend-registry')->assertSuccessful();
    $this->artisan('penova:frontend-registry', ['--check' => true])->assertSuccessful();

    $registry = (string) file_get_contents(GenerateFrontendRegistryCommand::path());
    // StudlyCase coordinate (not the kebab key), both scaffolded pages present.
    expect($registry)->toContain('"Modules/ReportHub/Index": () => import("@/Modules/ReportHub/Pages/Index.vue")');
    expect($registry)->toContain('"Modules/ReportHub/Create": () => import("@/Modules/ReportHub/Pages/Create.vue")');

    // The Index page links Create through the Workspace-path helper to the
    // module's own route — never the retired /admin prefix.
    $indexVue = (string) file_get_contents(resource_path('js/Modules/ReportHub/Pages/Index.vue'));
    expect($indexVue)->toContain("ws('/report-hubs/create')");
    expect($indexVue)->not->toContain('/admin/');
});
