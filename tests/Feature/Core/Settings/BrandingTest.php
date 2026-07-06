<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('branding shared prop falls back to config defaults when nothing is saved', function () {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Core/Welcome')
            ->where('branding.name', config('penova.branding.name'))
            ->where('branding.primary_color', '#01696f')
            ->where('branding.footer_text', config('penova.branding.footer_text')));
});

test('an owner can save branding and it overrides the config defaults', function () {
    $this->seed(\Database\Seeders\PenovaCoreSeeder::class);

    $this->post('/login', [
        'email' => config('penova.admin.email'),
        'password' => config('penova.admin.password'),
    ]);

    $this->put(route('penova.settings.update'), [
        'settings' => [
            'branding' => [
                'name' => 'Acme Store',
                'logo_url' => 'https://example.com/logo.png',
                'primary_color' => '#123456',
                'footer_text' => 'Powered by Acme',
            ],
        ],
    ])->assertRedirect();

    $this->assertDatabaseHas('settings', ['key' => 'branding']);

    // Fresh request (real requests are new processes; drop cached guards).
    $this->app['auth']->forgetGuards();

    $this->get('/')
        ->assertInertia(fn (Assert $page) => $page
            ->where('branding.name', 'Acme Store')
            ->where('branding.logo_url', 'https://example.com/logo.png'));
});

test('a blank branding field falls back to the config default, not an empty string', function () {
    $this->seed(\Database\Seeders\PenovaCoreSeeder::class);

    $this->post('/login', [
        'email' => config('penova.admin.email'),
        'password' => config('penova.admin.password'),
    ]);

    $this->put(route('penova.settings.update'), [
        'settings' => [
            'branding' => [
                'name' => '',
                'logo_url' => '',
                'primary_color' => '',
                'footer_text' => 'Powered by Acme',
            ],
        ],
    ])->assertRedirect();

    $this->app['auth']->forgetGuards();

    $this->get('/')
        ->assertInertia(fn (Assert $page) => $page
            ->where('branding.name', config('penova.branding.name'))
            ->where('branding.footer_text', 'Powered by Acme'));
});

test('an invalid logo url is rejected and nothing is saved', function () {
    $this->seed(\Database\Seeders\PenovaCoreSeeder::class);

    $this->post('/login', [
        'email' => config('penova.admin.email'),
        'password' => config('penova.admin.password'),
    ]);

    $this->put(route('penova.settings.update'), [
        'settings' => [
            'branding' => ['logo_url' => 'not-a-url'],
        ],
    ])->assertSessionHasErrors('settings.branding.logo_url');

    $this->assertDatabaseMissing('settings', ['key' => 'branding']);
});

test('saving branding alongside generic settings persists all of them', function () {
    // Guards the update loop against a `validated()` regression: nested
    // settings.branding.* rules would make validated() return only branding,
    // silently dropping sibling keys. Iterating input('settings') keeps them.
    $this->seed(\Database\Seeders\PenovaCoreSeeder::class);

    $this->post('/login', [
        'email' => config('penova.admin.email'),
        'password' => config('penova.admin.password'),
    ]);

    $this->put(route('penova.settings.update'), [
        'settings' => [
            'site_name' => 'Acme Panel',
            'contact_email' => 'hi@acme.test',
            'branding' => ['name' => 'Acme Store'],
        ],
    ])->assertRedirect();

    $this->assertDatabaseHas('settings', ['key' => 'site_name']);
    $this->assertDatabaseHas('settings', ['key' => 'contact_email']);
    $this->assertDatabaseHas('settings', ['key' => 'branding']);
});
