<?php

use App\Http\Controllers\WorkspaceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Penova Core Routes
|--------------------------------------------------------------------------
| Loaded by PenovaCoreServiceProvider under the "web" middleware group.
| Each Core module keeps its own routes.php next to its code
| (self-contained modules); this file only composes them.
|
| Product Modules do NOT add routes here — each module's service
| provider loads its own app/Modules/<Name>/routes.php.
*/

// Guest-facing auth (login, password reset, optional registration).
require app_path('Core/Auth/routes.php');

// Authenticated Workspace: routes register under the configured Workspace
// prefix (default /workspace), with route names penova.*
Route::middleware(config('penova.workspace.middleware'))
    ->prefix(config('penova.workspace.prefix'))
    ->as('penova.')
    ->group(function () {
        Route::get('/', WorkspaceController::class)->name('workspace');

        require app_path('Core/Users/routes.php');
        require app_path('Core/Roles/routes.php');
        require app_path('Core/Settings/routes.php');
        require app_path('Core/Notifications/routes.php');
        require app_path('Core/Logs/routes.php');
    });
