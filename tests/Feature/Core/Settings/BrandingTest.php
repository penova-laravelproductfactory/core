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
