<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

/**
 * Product-facing landing page at "/" — introduces Penova Core.
 *
 * Shown to everyone (guests and authenticated users); nobody is
 * redirected. The page reads the shared auth.user prop to adapt its
 * primary CTA (login vs. panel), so this controller needs no props.
 */
class WelcomeController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Core/Welcome');
    }
}
