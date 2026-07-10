<?php

use App\Core\Support\Commands\GenerateFrontendRegistryCommand;
use App\Core\Support\FrontendRegistry;

/**
 * P1 — the generate command in the CORE lane (no Module enabled). The registry
 * is a git-ignored build artifact: regenerated deterministically, provenance-
 * protected, and never source-controlled.
 */

beforeEach(fn () => @unlink(GenerateFrontendRegistryCommand::path()));
afterEach(fn () => @unlink(GenerateFrontendRegistryCommand::path()));

test('the command generates a valid, git-ignored, empty registry in the Core lane', function () {
    $this->artisan('penova:frontend-registry')->assertSuccessful();

    $content = (string) file_get_contents(GenerateFrontendRegistryCommand::path());
    expect($content)->toContain('export const moduleWidgets = {};');   // Core enables no Module
    expect($content)->toContain('export const modulePages = {};');
    expect(FrontendRegistry::verifyIntegrity($content))->toBeTrue();

    // The artifact path is git-ignored — never source-controlled.
    expect((string) file_get_contents(base_path('.gitignore')))->toContain('/resources/js/generated');
});

test('regeneration is byte-for-byte deterministic', function () {
    $this->artisan('penova:frontend-registry')->assertSuccessful();
    $first = (string) file_get_contents(GenerateFrontendRegistryCommand::path());

    $this->artisan('penova:frontend-registry')->assertSuccessful();
    $second = (string) file_get_contents(GenerateFrontendRegistryCommand::path());

    expect($second)->toBe($first);
});

test('--check fails loudly when the registry is missing', function () {
    $this->artisan('penova:frontend-registry', ['--check' => true])
        ->assertFailed()
        ->expectsOutputToContain('missing');
});

test('--check passes for a freshly generated registry', function () {
    $this->artisan('penova:frontend-registry')->assertSuccessful();
    $this->artisan('penova:frontend-registry', ['--check' => true])->assertSuccessful();
});

test('--check fails loudly when the registry was hand-edited (provenance)', function () {
    $this->artisan('penova:frontend-registry')->assertSuccessful();
    $path = GenerateFrontendRegistryCommand::path();
    file_put_contents($path, str_replace('moduleWidgets', 'moduleHacked', (string) file_get_contents($path)));

    $this->artisan('penova:frontend-registry', ['--check' => true])
        ->assertFailed()
        ->expectsOutputToContain('hand-edited');
});

test('--check fails loudly when the registry is valid but stale', function () {
    // A validly-rendered artifact for a DIFFERENT (non-empty) module set: it
    // passes the integrity check but does not match the Core lane's fresh output.
    $stale = FrontendRegistry::render(FrontendRegistry::build([
        ['key' => 'ghost', 'source' => '@/Modules/Ghost', 'widgets' => [['key' => 'ghost-w', 'area' => 'core']], 'frontend' => ['widgets' => [['key' => 'ghost-w', 'entry' => 'Widgets/W']], 'pages' => []]],
    ]));
    $path = GenerateFrontendRegistryCommand::path();
    @mkdir(dirname($path), 0755, true);
    file_put_contents($path, $stale);
    expect(FrontendRegistry::verifyIntegrity($stale))->toBeTrue(); // valid...

    $this->artisan('penova:frontend-registry', ['--check' => true])
        ->assertFailed()
        ->expectsOutputToContain('stale'); // ...but stale
});
