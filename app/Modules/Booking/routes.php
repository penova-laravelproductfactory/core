<?php

use App\Modules\Booking\Controllers\BookingCreateController;
use App\Modules\Booking\Controllers\BookingEditController;
use App\Modules\Booking\Controllers\BookingIndexController;
use App\Modules\Booking\Controllers\BookingsTodayCountController;
use App\Modules\Booking\Controllers\BookingStoreController;
use App\Modules\Booking\Controllers\BookingUpdateController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Modules\Booking Routes
|--------------------------------------------------------------------------
| Plain route definitions only — BookingServiceProvider::boot() loads
| this file inside the panel group (admin URI prefix + auth middleware
| from config('penova.admin')), so URIs land at /admin/bookings/... .
| Route names carry the module's own "booking." prefix explicitly;
| "penova.*" names are reserved for Core.
*/

// Dashboard-widget data endpoint. Registered before the {booking}
// parameterised routes so "today-count" is never captured as a model key.
Route::get('/bookings/today-count', BookingsTodayCountController::class)->name('booking.today-count');

Route::get('/bookings', BookingIndexController::class)->name('booking.index');
Route::get('/bookings/create', BookingCreateController::class)->name('booking.create');
Route::post('/bookings', BookingStoreController::class)->name('booking.store');
Route::get('/bookings/{booking}/edit', BookingEditController::class)->name('booking.edit');
Route::match(['put', 'patch'], '/bookings/{booking}', BookingUpdateController::class)->name('booking.update');
