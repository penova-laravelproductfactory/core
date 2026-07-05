<?php

namespace App\Core\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Core\Auth — shows the reset-password form for the emailed token
 * (GET /reset-password/{token}).
 */
class ShowResetPasswordController extends Controller
{
    public function __invoke(Request $request): Response
    {
        return Inertia::render('Core/Auth/ResetPassword', [
            'email' => $request->email,
            'token' => $request->route('token'),
        ]);
    }
}
