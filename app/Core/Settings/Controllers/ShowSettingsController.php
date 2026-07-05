<?php

namespace App\Core\Settings\Controllers;

use App\Core\Settings\Services\SettingsManager;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Core\Settings — the panel Settings page (penova.settings.index).
 *
 * Products extend the settings surface by seeding more keys and adding
 * fields to resources/js/Core/Pages/Settings/Index.vue (or their own
 * module settings page) — never by forking this controller.
 */
class ShowSettingsController extends Controller
{
    public function __invoke(SettingsManager $settings): Response
    {
        return Inertia::render('Core/Settings/Index', [
            'settings' => $settings->all(),
        ]);
    }
}
