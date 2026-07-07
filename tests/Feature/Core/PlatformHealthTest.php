<?php

use App\Core\Support\PlatformHealth;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('platform health reports its subsystems with valid statuses', function () {
    $items = app(PlatformHealth::class)->check();

    expect($items)->not->toBeEmpty();

    foreach ($items as $item) {
        expect($item)->toHaveKeys(['key', 'label', 'status', 'detail']);
        expect($item['status'])->toBeIn(['ready', 'warning']);
    }
});
