<?php

namespace App\Http\Controllers;

use App\Core\Roles\Models\Role;
use App\Core\Settings\Services\SettingsManager;
use App\Core\Support\ManifestRegistry;
use App\Core\Support\PlatformHealth;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The Workspace — the post-install onboarding screen at the Workspace root.
 *
 * Assembles one first-class `platform` view-model (status only; navigation
 * stays the separate shared `menu` prop). Onboarding, installed modules,
 * health and What's New drive time-to-first-product — not statistics.
 */
class WorkspaceController extends Controller
{
    public function __invoke(
        Request $request,
        SettingsManager $settings,
        ManifestRegistry $registry,
        PlatformHealth $health,
    ): Response {
        $links = config('penova.links');
        $brandingConfigured = ! empty($settings->get('branding'));
        $hasModule = ! $registry->isEmpty();

        return Inertia::render('Core/Workspace/Index', [
            'platform' => [
                'version' => config('penova.version'),
                'links' => $links,

                // Onboarding copy is resolved to the active locale via __()
                // against the ui.home.onboarding catalog (RFC-005 / D-027 /
                // D-AUDIT-006) — English base, Persian under APP_LOCALE=fa — so the
                // Workspace home is not English-hardcoded. Step keys stay stable
                // identifiers; only labels/descriptions/CTAs are localized.
                'onboarding' => [
                    'steps' => [
                        ['key' => 'core-installed', 'label' => __('ui.home.onboarding.step_core_installed'), 'done' => true],
                        ['key' => 'authentication', 'label' => __('ui.home.onboarding.step_authentication'), 'done' => true],
                        ['key' => 'admin-panel', 'label' => __('ui.home.onboarding.step_workspace_ready'), 'done' => true],
                        ['key' => 'branding', 'label' => __('ui.home.onboarding.step_branding'), 'done' => $brandingConfigured,
                            'cta' => ['label' => __('ui.home.onboarding.cta_configure'), 'href' => '/'.config('penova.workspace.prefix').'/settings']],
                        ['key' => 'first-module', 'label' => __('ui.home.onboarding.step_first_module'), 'done' => $hasModule,
                            'cta' => ['label' => __('ui.home.onboarding.cta_browse_docs'), 'href' => $links['documentation']]],
                    ],
                    'guidance' => [
                        ['key' => 'first-resource', 'label' => __('ui.home.onboarding.guide_resource_label'),
                            'description' => __('ui.home.onboarding.guide_resource_desc'),
                            'cta' => ['label' => __('ui.home.onboarding.cta_guide'), 'href' => $links['documentation']]],
                        ['key' => 'first-product', 'label' => __('ui.home.onboarding.guide_product_label'),
                            'description' => __('ui.home.onboarding.guide_product_desc'),
                            'cta' => ['label' => __('ui.home.onboarding.cta_guide'), 'href' => $links['documentation']]],
                    ],
                ],

                'modules' => $registry->all(),

                'overview' => [
                    'users' => User::count(),
                    'roles' => Role::count(),
                    'unread' => $request->user()->unreadNotifications()->count(),
                ],

                'health' => $health->check(),

                'brandingConfigured' => $brandingConfigured,

                'whatsNew' => config('penova.changelog')[0] ?? null,
            ],
        ]);
    }
}
