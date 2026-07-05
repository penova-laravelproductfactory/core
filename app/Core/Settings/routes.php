<?php

use App\Core\Settings\Controllers\ShowSettingsController;
use App\Core\Settings\Controllers\UpdateSettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Core\Settings Routes
|--------------------------------------------------------------------------
| Loaded by routes/penova.php inside the admin group.
| One invokable controller per action (the panel-wide convention).
*/

Route::middleware('permission:settings.manage')->group(function () {
    Route::get('settings', ShowSettingsController::class)->name('settings.index');
    Route::put('settings', UpdateSettingsController::class)->name('settings.update');
});
