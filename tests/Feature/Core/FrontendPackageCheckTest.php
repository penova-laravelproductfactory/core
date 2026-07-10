<?php

use App\Core\Support\FrontendPackageCheck;

/**
 * P4 — EXPERIMENTAL frontend PACKAGE PAIRING + PEER checks (RFC-006 / D-028),
 * loud before runtime. Generic/contract test: no real Module — fake pairings and
 * injected installed/Core version maps exercise the two named failures directly.
 */

function paired(array $package, array $overrides = []): array
{
    return [array_replace(['key' => 'acme', 'package' => $package], $overrides)];
}

$peers = ['vue' => '^3.5', '@inertiajs/vue3' => '^2.0'];
$core = ['vue' => '3.5.13', '@inertiajs/vue3' => '2.0.11', 'tailwindcss' => '4.0.0'];

test('a matched package with compatible peers passes', function () use ($peers, $core) {
    FrontendPackageCheck::verify(
        paired(['name' => '@acme/catalog-frontend', 'version' => '^1.0', 'peers' => $peers]),
        ['@acme/catalog-frontend' => '1.2.3'],
        $core,
    );
})->throwsNoExceptions();

test('module frontend package mismatch — the paired package is not installed', function () use ($core) {
    FrontendPackageCheck::verify(
        paired(['name' => '@acme/catalog-frontend', 'version' => '^1.0']),
        [], // nothing installed
        $core,
    );
})->throws(InvalidArgumentException::class, 'module frontend package mismatch');

test('module frontend package mismatch — installed major differs from the declared pairing', function () use ($core) {
    FrontendPackageCheck::verify(
        paired(['name' => '@acme/catalog-frontend', 'version' => '^1.0']),
        ['@acme/catalog-frontend' => '2.0.0'], // major 2 ≠ declared major 1
        $core,
    );
})->throws(InvalidArgumentException::class, 'major version differs');

test('module frontend peer incompatible — Core provides a different peer major', function () use ($core) {
    FrontendPackageCheck::verify(
        paired(['name' => '@acme/catalog-frontend', 'version' => '^1.0', 'peers' => ['vue' => '^2.0']]),
        ['@acme/catalog-frontend' => '1.0.0'],
        $core, // Core ships vue 3.x
    );
})->throws(InvalidArgumentException::class, 'module frontend peer incompatible');

test('module frontend peer incompatible — Core declares no such peer', function () use ($core) {
    FrontendPackageCheck::verify(
        paired(['name' => '@acme/catalog-frontend', 'version' => '^1.0', 'peers' => ['svelte' => '^4.0']]),
        ['@acme/catalog-frontend' => '1.0.0'],
        $core,
    );
})->throws(InvalidArgumentException::class, 'Core declares no [svelte]');

test('a Module with no declared package is a no-op (in-repo frontend needs no pairing)', function () {
    FrontendPackageCheck::verify([], [], []);
})->throwsNoExceptions();

test('major() normalizes range operators and a v prefix', function () {
    expect(FrontendPackageCheck::major('^3.5.0'))->toBe('3');
    expect(FrontendPackageCheck::major('~2.0'))->toBe('2');
    expect(FrontendPackageCheck::major('>=6.0.11'))->toBe('6');
    expect(FrontendPackageCheck::major('v1.4.2'))->toBe('1');
    expect(FrontendPackageCheck::major('0.1.0'))->toBe('0');
});
