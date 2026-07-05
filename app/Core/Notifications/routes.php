<?php

use App\Core\Notifications\Controllers\ListNotificationsController;
use App\Core\Notifications\Controllers\MarkNotificationReadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Core\Notifications Routes
|--------------------------------------------------------------------------
| Loaded by routes/penova.php inside the admin group. No extra permission:
| every authenticated panel user has a notification feed.
*/

Route::get('notifications', ListNotificationsController::class)->name('notifications.index');
Route::put('notifications/{id}/read', MarkNotificationReadController::class)->name('notifications.read');
