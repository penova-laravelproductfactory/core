<?php

use App\Core\Logs\Controllers\ListActivityLogsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Core\Logs Routes
|--------------------------------------------------------------------------
| Loaded by routes/penova.php inside the admin group.
*/

Route::middleware('permission:logs.view')->group(function () {
    Route::get('logs', ListActivityLogsController::class)->name('logs.index');
});
