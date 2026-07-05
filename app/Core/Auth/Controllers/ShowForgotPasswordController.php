<?php

namespace App\Core\Auth\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Core\Auth — shows the "forgot password" page (GET /forgot-password).
 */
class ShowForgotPasswordController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Core/Auth/ForgotPassword', [
            'status' => session('status'),
        ]);
    }
}
