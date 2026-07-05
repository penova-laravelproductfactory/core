<?php

namespace App\Core\Auth\Controllers;

use App\Core\Auth\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

/**
 * Core\Auth — handles a login attempt (POST /login).
 *
 * Pro version hooks: two-factor challenge after authenticate(),
 * email-verification gate, and single-session enforcement all slot in
 * here without touching callers.
 */
class LoginController extends Controller
{
    public function __invoke(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // The panel dashboard (penova.dashboard) is the canonical landing
        // zone; intended() still honours a deep link the guest originally
        // requested (e.g. /admin/users → login → back to /admin/users).
        return redirect()->intended(route('penova.dashboard'));
    }
}
