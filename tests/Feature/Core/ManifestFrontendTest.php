<?php

use App\Core\Support\Manifest;

/**
 * P0 — the EXPERIMENTAL frontend descriptor (RFC-006 / D-028): structural
 * validation and its named failure categories at Manifest-construction time.
 * The registration-time categories (unknown target area, unresolved entry,
 * incompatible peer/Core version, global cross-Module uniqueness) belong to the
 * registry generator (P1) and are not exercised here.
 */

test('a valid frontend section is declared and read back normalized', function () {
    $m = Manifest::for('store', 'Store', 'Commerce', '1.0.0')->frontend([
        'widgets' => [
            ['key' => 'store-active-products', 'entry' => 'widgets/ActiveProductsCard'],
        ],
        'pages' => [
            ['name' => 'Store/Products/Index', 'entry' => 'pages/Products/Index'],
        ],
    ]);

    // A frontend widget entry owns {key, entry} only — area/title/order stay the
    // backend widget descriptor's, joined by the globally-unique key.
    expect($m->frontendContributions())->toBe([
        'widgets' => [['key' => 'store-active-products', 'entry' => 'widgets/ActiveProductsCard']],
        'pages' => [['name' => 'Store/Products/Index', 'entry' => 'pages/Products/Index']],
    ]);
    expect($m->toArray())->toHaveKey('frontend');
});

test('missing sections default to empty and the Manifest stays immutable', function () {
    $base = Manifest::for('store', 'Store', 'Commerce', '1.0.0');
    $withFrontend = $base->frontend(['widgets' => [['key' => 'w', 'entry' => 'widgets/W']]]);

    expect($withFrontend->frontendContributions())
        ->toBe(['widgets' => [['key' => 'w', 'entry' => 'widgets/W']], 'pages' => []]);
    // The original declaration is untouched — the section is a fresh instance.
    expect($base->frontendContributions())->toBe(['widgets' => [], 'pages' => []]);
});

test('an unknown section is a malformed-descriptor failure', function () {
    Manifest::for('store', 'Store', 'Commerce', '1.0.0')->frontend(['layouts' => []]);
})->throws(InvalidArgumentException::class, 'malformed descriptor — unknown section [layouts]');

test('a widget entry missing a required field is malformed', function () {
    Manifest::for('store', 'Store', 'Commerce', '1.0.0')->frontend(['widgets' => [['key' => 'w']]]); // no entry
})->throws(InvalidArgumentException::class, 'field [entry] must be a non-empty string');

test('a path-traversal entry token is rejected as malformed', function () {
    Manifest::for('store', 'Store', 'Commerce', '1.0.0')->frontend(['pages' => [['name' => 'P', 'entry' => '../secrets/Index']]]);
})->throws(InvalidArgumentException::class, 'invalid entry token');

test('a leading-slash entry token is rejected as malformed', function () {
    Manifest::for('store', 'Store', 'Commerce', '1.0.0')->frontend(['widgets' => [['key' => 'w', 'entry' => '/widgets/W']]]);
})->throws(InvalidArgumentException::class, 'invalid entry token');

test('a duplicate widget key is a duplicate-contribution failure', function () {
    Manifest::for('store', 'Store', 'Commerce', '1.0.0')->frontend(['widgets' => [
        ['key' => 'dup', 'entry' => 'widgets/A'],
        ['key' => 'dup', 'entry' => 'widgets/B'],
    ]]);
})->throws(InvalidArgumentException::class, 'duplicate contribution — [widgets] key [dup]');

test('a duplicate page name is a duplicate-contribution failure', function () {
    Manifest::for('store', 'Store', 'Commerce', '1.0.0')->frontend(['pages' => [
        ['name' => 'Same', 'entry' => 'pages/A'],
        ['name' => 'Same', 'entry' => 'pages/B'],
    ]]);
})->throws(InvalidArgumentException::class, 'duplicate contribution — [pages] name [Same]');
