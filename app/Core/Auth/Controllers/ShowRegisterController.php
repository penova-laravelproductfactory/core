<?php

namespace App\Core\Auth\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Core\Auth — shows the self-registration page (GET /register).
 *
 * Only routed when config('penova.auth.registration') is true; products
 * toggle it via PENOVA_REGISTRATION in .env (see routes.php).
 */
class ShowRegisterController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Core/Auth/Register');
    }
}
