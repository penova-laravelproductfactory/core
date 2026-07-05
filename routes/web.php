<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
| Product-specific public routes live here (e.g. a CMS front site or a
| booking widget). Panel + auth routes belong to routes/penova.php,
| loaded by PenovaCoreServiceProvider.
*/

Route::get('/', fn () => redirect()->route('penova.dashboard'));
