<?php

use App\Modules\Demo\Controllers\ShowDemoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Modules\Demo Routes
|--------------------------------------------------------------------------
| Loaded by DemoServiceProvider::boot(). Modules reuse the panel's
| middleware + URI prefix from config but own their route-name prefix
| (demo.*) — Core's "penova.*" names are reserved for Core.
*/

Route::middleware(config('penova.admin.middleware'))
    ->prefix(config('penova.admin.prefix'))
    ->as('demo.')
    ->group(function () {
        Route::get('demo', ShowDemoController::class)->name('index');
    });
