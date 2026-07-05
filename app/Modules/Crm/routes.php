<?php

use App\Modules\Crm\Controllers\LeadCreateController;
use App\Modules\Crm\Controllers\LeadIndexController;
use App\Modules\Crm\Controllers\LeadsTodayCountController;
use App\Modules\Crm\Controllers\LeadStoreController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Modules\Crm Routes
|--------------------------------------------------------------------------
| Plain route definitions only — CrmServiceProvider::boot() loads this
| file inside the panel group (admin URI prefix + auth middleware from
| config('penova.admin')), so URIs land at /admin/leads/... .
| Route names carry the module's own "crm." prefix explicitly.
|
| Permissions (seeded by CrmPermissionsSeeder):
|   crm.leads.view   → index + the widget's today-count JSON
|   crm.leads.manage → create / store
*/

Route::middleware('permission:crm.leads.view')->group(function () {
    // Dashboard-widget data endpoint (JSON only, never in the menu).
    Route::get('/leads/today-count', LeadsTodayCountController::class)->name('crm.leads.today-count');

    Route::get('/leads', LeadIndexController::class)->name('crm.leads.index');
});

Route::middleware('permission:crm.leads.manage')->group(function () {
    Route::get('/leads/create', LeadCreateController::class)->name('crm.leads.create');
    Route::post('/leads', LeadStoreController::class)->name('crm.leads.store');
});
