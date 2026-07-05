<?php

namespace App\Modules\Demo\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Modules\Demo — the module's landing page (demo.index).
 * One invokable controller per action, like everywhere in the panel.
 */
class ShowDemoController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Modules/Demo/Index');
    }
}
