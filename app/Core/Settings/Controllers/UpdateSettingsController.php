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
        $request->validate([
            'settings' => ['required', 'array'],
            'settings.branding.name' => ['nullable', 'string', 'max:100'],
            'settings.branding.logo_url' => ['nullable', 'url', 'max:255'],
            'settings.branding.primary_color' => ['nullable', 'string', 'max:20'],
            'settings.branding.footer_text' => ['nullable', 'string', 'max:255'],
        ]);

        // Iterate the raw input, NOT validated(): adding nested
        // settings.branding.* rules makes validated() return only the ruled
        // keys, which would silently drop generic settings like site_name.
        foreach ($request->input('settings') as $key => $value) {
            $settings->set($key, $value);
        }

        return back()->with('success', __('Settings saved.'));
    }
}
