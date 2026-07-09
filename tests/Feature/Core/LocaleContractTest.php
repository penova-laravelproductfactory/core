<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * Locale contract (RFC-005 / D-027). These defend the promise, not the
 * mechanism: Core is locale-neutral — English by default and LTR; Persian is
 * an opt-in locale that renders Persian and RTL; a translation missing from
 * the active locale falls back to English; and canonical identifiers (menu
 * keys, resolved routes) are identical in every locale — only the human-facing
 * label changes.
 *
 * The shared Inertia props (locale / direction / messages / menu) come from
 * HandleInertiaRequests and ride on every page, so a guest GET /login is
 * enough to observe the promise without seeding or signing in.
 */

uses(RefreshDatabase::class);

test('the default locale renders English and left-to-right', function () {
    // English is Core's shipped default (config app.locale) — the unconfigured
    // install, no deployment opt-in.
    App::setLocale('en');

    $this->get('/login')->assertInertia(fn (Assert $page) => $page
        ->where('locale', 'en')
        ->where('direction', 'ltr')
        ->where('messages.common.save', 'Save'));
});

test('the Persian locale renders Persian and right-to-left', function () {
    // The deployment opt-in a Persian install makes (APP_LOCALE=fa).
    App::setLocale('fa');

    $this->get('/login')->assertInertia(fn (Assert $page) => $page
        ->where('locale', 'fa')
        ->where('direction', 'rtl')
        ->where('messages.common.save', 'ذخیره'));
});

test('a translation key missing from the active locale falls back to English', function () {
    // Ensure the English ui group is loaded from file, then add a line that
    // exists ONLY in the English base — the fa catalog has no such key.
    __('ui.common.save');
    app('translator')->addLines(['ui.__contract_probe__' => 'English base value'], 'en');

    App::setLocale('fa');

    // fa has no translation for the probe, so it resolves to the English base
    // (config app.fallback_locale = en).
    expect(__('ui.__contract_probe__'))->toBe('English base value');
});

test('canonical identifiers are unchanged across locales', function () {
    $workspaceHref = route('penova.workspace', absolute: false);

    // The permission-free Workspace menu item is shared on every page. Its
    // identity (key) and resolved route (href) are contract; the label is
    // presentation. Identity/route must not move when the locale changes.
    $workspaceItem = fn ($menu) => collect($menu)->firstWhere('key', 'workspace');

    App::setLocale('en');
    $this->get('/login')->assertInertia(fn (Assert $page) => $page
        ->where('menu', fn ($menu) => $workspaceItem($menu)['key'] === 'workspace'
            && $workspaceItem($menu)['href'] === $workspaceHref
            && $workspaceItem($menu)['label'] === 'Workspace'));

    App::setLocale('fa');
    $this->get('/login')->assertInertia(fn (Assert $page) => $page
        ->where('menu', fn ($menu) => $workspaceItem($menu)['key'] === 'workspace'      // identity unchanged
            && $workspaceItem($menu)['href'] === $workspaceHref                          // route unchanged
            && $workspaceItem($menu)['label'] === 'میزکار'));                            // only the label localizes
});
