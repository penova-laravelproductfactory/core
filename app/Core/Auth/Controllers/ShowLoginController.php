<?php

namespace App\Core\Auth\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Core\Auth — shows the login page (GET /login).
 *
 * Responsibility of the Auth module: authenticate users into the panel.
 * Products never duplicate this; they only theme the Inertia pages under
 * resources/js/Core/Pages/Auth.
 *
 * The full session flow:
 *   guest hits any /admin URL → redirected to /login
 *     (redirectGuestsTo in bootstrap/app.php)
 *   successful login → redirect()->intended(penova.dashboard),
 *     so a deep link like /admin/users survives the login round-trip
 *   logout → session invalidated + token regenerated → /login
 */
class ShowLoginController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Core/Auth/Login', [
            'canRegister' => (bool) config('penova.auth.registration'),
            // Status messages from other auth flows, e.g. "password reset".
            'status' => session('status'),
            // A guest bounced here from the store checkout gets a small
            // contextual notice ("log in to continue your order").
            'checkoutIntent' => str_contains(session('url.intended', ''), '/store/checkout'),
        ]);
    }
}
