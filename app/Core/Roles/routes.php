<?php

use App\Core\Roles\Controllers\DeleteRoleController;
use App\Core\Roles\Controllers\ListRolesController;
use App\Core\Roles\Controllers\StoreRoleController;
use App\Core\Roles\Controllers\UpdateRoleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Core\Roles Routes
|--------------------------------------------------------------------------
| Loaded by routes/penova.php inside the admin group
| (prefix + auth middleware + "penova." name prefix already applied).
| One invokable controller per action (the panel-wide convention).
*/

Route::middleware('permission:roles.manage')->group(function () {
    Route::get('roles', ListRolesController::class)->name('roles.index');
    Route::post('roles', StoreRoleController::class)->name('roles.store');
    Route::put('roles/{role}', UpdateRoleController::class)->name('roles.update');
    Route::delete('roles/{role}', DeleteRoleController::class)->name('roles.destroy');
});
