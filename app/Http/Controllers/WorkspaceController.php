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
 * The Workspace — the post-install onboarding screen at /admin.
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

                'onboarding' => [
                    'steps' => [
                        ['key' => 'core-installed', 'label' => 'Penova Core installed', 'done' => true],
                        ['key' => 'authentication', 'label' => 'Authentication ready', 'done' => true],
                        ['key' => 'admin-panel', 'label' => 'Admin panel ready', 'done' => true],
                        ['key' => 'branding', 'label' => 'Configure branding', 'done' => $brandingConfigured,
                            'cta' => ['label' => 'Configure', 'href' => '/admin/settings']],
                        ['key' => 'first-module', 'label' => 'Install your first module', 'done' => $hasModule,
                            'cta' => ['label' => 'Browse docs', 'href' => $links['documentation']]],
                    ],
                    'guidance' => [
                        ['key' => 'first-resource', 'label' => 'Create your first Resource',
                            'description' => 'Scaffold a CRUD resource with the module generator.',
                            'cta' => ['label' => 'Guide', 'href' => $links['documentation']]],
                        ['key' => 'first-product', 'label' => 'Build your first Product',
                            'description' => 'Compose modules into a shippable Laravel product.',
                            'cta' => ['label' => 'Guide', 'href' => $links['documentation']]],
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
