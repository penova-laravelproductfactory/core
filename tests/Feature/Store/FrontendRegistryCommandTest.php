<?php

use App\Core\Support\Commands\GenerateFrontendRegistryCommand;
use App\Core\Support\FrontendRegistry;

/**
 * Combined P2/P3 — the generate command in the STORE lane (Store enabled, now
 * declaring its typed `frontend` entries). The registry is populated with Store's
 * widget + pages, deterministic, provenance-protected, git-ignored, and every
 * entry resolves through Store's OWN coordinate — with a real Module enabled and
 * Core naming no Module. Neither lane's output is source-controlled truth.
 */

beforeEach(fn () => @unlink(GenerateFrontendRegistryCommand::path()));
afterEach(fn () => @unlink(GenerateFrontendRegistryCommand::path()));

test('the command generates Store\'s registry entries deterministically and they resolve', function () {
    $this->artisan('penova:frontend-registry')->assertSuccessful(); // --check resolver would fail if any entry did not resolve
    $first = (string) file_get_contents(GenerateFrontendRegistryCommand::path());

    // Registry OUTPUT: Store's widget (by key) and a page (by name) → import specifiers.
    expect($first)->toContain("\"store-active-products\": () => import(\"@/Modules/Store/Widgets/ActiveProductsCard.vue\")");
    expect($first)->toContain("\"Modules/Store/Products/Index\": () => import(\"@/Modules/Store/Pages/Products/Index.vue\")");
    expect(FrontendRegistry::verifyIntegrity($first))->toBeTrue();

    // Deterministic + up to date.
    $this->artisan('penova:frontend-registry')->assertSuccessful();
    expect((string) file_get_contents(GenerateFrontendRegistryCommand::path()))->toBe($first);
    $this->artisan('penova:frontend-registry', ['--check' => true])->assertSuccessful();
});
