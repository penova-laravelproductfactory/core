<?php

namespace App\Core\Settings\Controllers;

use App\Core\Settings\Services\SettingsManager;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Core\Settings — persists edited settings (penova.settings.update).
 */
class UpdateSettingsController extends Controller
{
    public function __invoke(Request $request, SettingsManager $settings): RedirectResponse
    {
        $validated = $request->validate([
            'settings' => ['required', 'array'],
        ]);

        foreach ($validated['settings'] as $key => $value) {
            $settings->set($key, $value);
        }

        return back()->with('success', __('Settings saved.'));
    }
}
