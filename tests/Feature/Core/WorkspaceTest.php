<?php

use App\Core\Settings\Services\SettingsManager;
use App\Core\Support\ManifestRegistry;
use App\Core\Roles\Models\Role;
use App\Models\User;
use Database\Seeders\PenovaCoreSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

function loginWorkspaceAdmin(): void
{
    test()->seed(PenovaCoreSeeder::class);
    test()->post('/login', [
        'email' => config('penova.operator.email'),
        'password' => config('penova.operator.password'),
    ]);
}

test('workspace renders the platform view-model', function () {
    loginWorkspaceAdmin();

    $this->get(route('penova.workspace'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Core/Workspace/Index')
            ->has('platform.version')
            ->has('platform.links.documentation')
            ->has('platform.onboarding.steps')
            ->has('platform.onboarding.guidance')
            ->has('platform.health')
            ->has('platform.overview')
            ->has('platform.whatsNew'));
});

test('platform health lists its subsystems', function () {
    loginWorkspaceAdmin();

    $this->get(route('penova.workspace'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('platform.health', fn ($health) => collect($health)->isNotEmpty()
                && collect($health)->every(fn ($item) => isset($item['key'], $item['status']))));
});

test('platform lists installed module manifests', function () {
    loginWorkspaceAdmin();

    $this->get(route('penova.workspace'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('platform.modules', fn ($modules) => collect($modules)->contains('key', 'store')));
});

test('platform modules is empty when no modules are installed', function () {
    loginWorkspaceAdmin();

    config(['penova.modules' => []]);
    app()->forgetInstance(ManifestRegistry::class);

    $this->get(route('penova.workspace'))
        ->assertInertia(fn (Assert $page) => $page->where('platform.modules', []));
});

test('the branding onboarding step flips to done after branding is saved', function () {
    loginWorkspaceAdmin();

    $brandingStep = fn ($steps) => collect($steps)->firstWhere('key', 'branding')['done'];

    $this->get(route('penova.workspace'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('platform.brandingConfigured', false)
            ->where('platform.onboarding.steps', fn ($steps) => $brandingStep($steps) === false));

    app(SettingsManager::class)->set('branding', ['name' => 'Acme']);

    $this->get(route('penova.workspace'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('platform.brandingConfigured', true)
            ->where('platform.onboarding.steps', fn ($steps) => $brandingStep($steps) === true));
});

test('platform overview reflects seeded counts', function () {
    loginWorkspaceAdmin();

    $this->get(route('penova.workspace'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('platform.overview.users', User::count())
            ->where('platform.overview.roles', Role::count()));
});
