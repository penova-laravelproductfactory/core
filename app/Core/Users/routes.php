<?php

use App\Core\Users\Controllers\CreateUserController;
use App\Core\Users\Controllers\DeleteUserController;
use App\Core\Users\Controllers\EditUserController;
use App\Core\Users\Controllers\ListUsersController;
use App\Core\Users\Controllers\StoreUserController;
use App\Core\Users\Controllers\UpdateUserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Core\Users Routes
|--------------------------------------------------------------------------
| Loaded by routes/penova.php inside the admin group.
| Route names resolve to penova.users.* — one invokable controller per
| action (the panel-wide convention).
*/

Route::middleware('permission:users.manage')->group(function () {
    Route::get('users', ListUsersController::class)->name('users.index');
    Route::get('users/create', CreateUserController::class)->name('users.create');
    Route::post('users', StoreUserController::class)->name('users.store');
    Route::get('users/{user}/edit', EditUserController::class)->name('users.edit');
    Route::match(['put', 'patch'], 'users/{user}', UpdateUserController::class)->name('users.update');
    Route::delete('users/{user}', DeleteUserController::class)->name('users.destroy');
});
