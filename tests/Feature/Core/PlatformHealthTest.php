<?php

use App\Core\Support\PlatformHealth;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('platform health reports the five subsystems with valid statuses', function () {
    $items = app(PlatformHealth::class)->check();

    expect($items)->toHaveCount(5);

    $keys = collect($items)->pluck('key')->sort()->values()->all();
    expect($keys)->toBe(['cache', 'database', 'laravel', 'queue', 'storage']);

    foreach ($items as $item) {
        expect($item)->toHaveKeys(['key', 'label', 'status', 'detail']);
        expect($item['status'])->toBeIn(['ready', 'warning']);
    }
});
