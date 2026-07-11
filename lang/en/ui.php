<?php

/*
|--------------------------------------------------------------------------
| Core UI messages — English base catalog (RFC-005 / D-027)
|--------------------------------------------------------------------------
| The base and fallback language. Keys are English-shaped and stable; a
| locale catalog (e.g. lang/fa/ui.php) overrides values, and any key missing
| there falls back to this file. These are the frontend (Vue) messages shared
| with every page by HandleInertiaRequests; server-rendered strings use
| Laravel's native __() against the same catalogs.
|
| Externalization of Core's UI strings into this catalog proceeds by area;
| this file grows as each area is migrated off hardcoded literals.
*/

return [
    'common' => [
        'save' => 'Save',
        'cancel' => 'Cancel',
        'logout' => 'Log out',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'name' => 'Name',
        'email' => 'Email',
        'role' => 'Role',
        'back_to_list' => 'Back to list',
        'close' => 'Close',
    ],

    'status' => [
        'ready' => 'Ready',
        'warning' => 'Warning',
    ],

    // DataTable (Core + Module tables). The search placeholder is only the
    // default when a table omits its own; Module tables passing a specific
    // placeholder keep it (their string, their concern).
    'table' => [
        'search_placeholder' => 'Search…',
        'no_results' => 'No results found.',
    ],

    // WidgetRenderer fail-soft message when a descriptor's .vue is missing.
    // The component path stays verbatim (an identifier), wrapped LTR by the
    // template, so the message is split around it.
    'render' => [
        'widget_missing_before' => 'Widget “',
        'widget_missing_after' => '” not found.',
    ],

    'shell' => [
        'workspace_subtitle' => 'Your product workspace',
        'tagline' => 'Laravel Product Factory',
        'guest_footer' => 'A product core, built on Laravel',
    ],

    // Sidebar labels for Core-owned menu items. Resolved server-side against
    // each item's 'label_key' marker (RFC-005 / D-027, menu Option B); Module
    // menu items keep their own literal 'label' and are never touched here.
    'nav' => [
        'workspace' => 'Workspace',
        'users' => 'Users',
        'roles' => 'Roles & permissions',
        'settings' => 'Settings',
        'logs' => 'Activity log',
        'notifications' => 'Notifications',
    ],

    'workspace' => [
        'title' => 'Workspace',
        'subtitle' => 'Penova platform management',
    ],

    'users' => [
        'title' => 'Users',
        'subtitle' => 'Manage workspace user accounts',
        'new' => 'New user',
        'col_created' => 'Created',
        'confirm_delete' => 'Delete user “:name”?',
        'create_title' => 'New user',
        'edit_title' => 'Edit user',
        'edit_document_title' => 'Edit user — :name',
        'password' => 'Password',
        'password_confirm' => 'Confirm password',
        'password_new' => 'New password (leave blank to keep current)',
        'password_new_confirm' => 'Confirm new password',
        'roles_legend' => 'Roles',
        'create_submit' => 'Create user',
        'save_changes' => 'Save changes',
    ],

    'roles' => [
        'title' => 'Roles & permissions',
        'subtitle' => 'Role-based access control',
        'new' => 'New role',
        'edit_title' => 'Edit role',
        'col_slug' => 'Slug',
        'col_users_count' => 'Users',
        'permissions_legend' => 'Permissions',
    ],

    'settings' => [
        'title' => 'Settings',
        'subtitle' => 'Site configuration, editable by administrators',
        'site_name' => 'Site name',
        'contact_email' => 'Contact email',
        'branding_card' => 'White Label / Branding',
        'branding_help' => 'Set the Core brand name and branding for the workspace and welcome page.',
        'brand_name' => 'Brand name',
        'logo_url' => 'Logo URL',
        'primary_color' => 'Primary color (hex)',
        'footer_text' => 'Footer text',
        'save' => 'Save settings',
    ],

    'notifications' => [
        'title' => 'Notifications',
        'subtitle' => 'Your account notifications',
        'mark_all_read' => 'Mark all as read',
        'mark_read' => 'Mark read',
        'empty' => 'You have no notifications yet.',
    ],

    'logs' => [
        'title' => 'Activity log',
        'subtitle' => 'Who, what, and when',
        'col_time' => 'Time',
        'col_user' => 'User',
        'col_action' => 'Action',
        'col_subject' => 'Subject',
        'system' => 'System',
    ],

    // Guest-auth presentation only (login, register, password reset). Auth
    // behavior, route names, and the server-provided `status` message are not
    // here — they are owned by the auth flow, not this catalog (D-017).
    'auth' => [
        'sign_in' => 'Sign in',
        'login_document_title' => 'Sign in to the workspace',
        'password' => 'Password',
        'password_confirm' => 'Confirm password',
        'password_new' => 'New password',
        'password_new_confirm' => 'Confirm new password',
        'remember_me' => 'Remember me',
        'forgot_link' => 'Forgot your password?',
        'register' => 'Create an account',
        'register_submit' => 'Create account',
        'register_help' => 'If you are a system administrator and need a new account, complete this form.',
        'have_account' => 'Already have an account? Sign in',
        'forgot_title' => 'Reset your password',
        'forgot_help' => 'Enter your account email and we will send you a password reset link.',
        'forgot_submit' => 'Send reset link',
        'reset_title' => 'Set a new password',
        'reset_submit' => 'Save password',
    ],

    // Workspace home (post-install onboarding) — Core-owned presentation only.
    // Onboarding steps, guidance, module names, health rows and overview values
    // come from the backend `platform` view-model and stay as data.
    'home' => [
        'hero_welcome' => 'Welcome to Penova Core',
        'hero_tagline' => 'Start your next Laravel product in minutes instead of weeks.',
        'link_documentation' => 'Documentation',
        'link_release_notes' => 'Release Notes',
        'get_started' => 'Get started',
        'gs_module_title' => 'Create your first Module',
        'gs_module_desc' => 'Scaffold a new business module with the generator.',
        'configure_branding' => 'Configure Branding',
        'gs_branding_desc' => 'Make the Workspace yours before shipping.',
        'gs_resource_title' => 'Create your first Resource',
        'gs_resource_desc' => 'Generate a CRUD resource in minutes.',
        'gs_users_title' => 'Manage Users & Roles',
        'gs_users_desc' => 'Control who can access what.',
        'gs_docs_title' => 'Read Documentation',
        'gs_docs_desc' => 'Everything you need to ship faster.',
        'setup_heading' => 'Platform setup',
        'keep_building' => 'Keep building',
        'overview_heading' => 'Overview',
        'overview_users' => 'Users',
        'overview_roles' => 'Roles',
        'overview_unread' => 'Unread Notifications',
        'health_heading' => 'Platform Health',
        'modules_heading' => 'Installed Modules',
        'modules_empty_title' => 'Your platform is ready.',
        'modules_empty_body' => 'Install your first business module to turn Penova Core into a real product.',
        'branding_reminder_title' => 'Your product still uses the default Penova branding.',
        'branding_reminder_body' => 'Configure your logo, application name and footer before shipping.',
        'whats_new' => 'What’s New in v:version',

        // Onboarding steps + guidance assembled by WorkspaceController (RFC-005 /
        // D-027 / D-AUDIT-006). Keyed so the Workspace home is locale-aware rather
        // than English-hardcoded; resolved server-side via __() against these keys.
        'onboarding' => [
            'step_core_installed' => 'Penova Core installed',
            'step_authentication' => 'Authentication ready',
            'step_workspace_ready' => 'Workspace ready',
            'step_branding' => 'Configure branding',
            'step_first_module' => 'Install your first module',
            'guide_resource_label' => 'Create your first Resource',
            'guide_resource_desc' => 'Scaffold a CRUD resource with the module generator.',
            'guide_product_label' => 'Build your first Product',
            'guide_product_desc' => 'Compose modules into a shippable Laravel product.',
            'cta_configure' => 'Configure',
            'cta_browse_docs' => 'Browse docs',
            'cta_guide' => 'Guide',
        ],
    ],

    // Core widget copy (the widgets Core itself ships). Widget descriptor
    // titles and all row data come from the backend; only the components'
    // own literals live here.
    'widgets' => [
        'users_title' => 'Users',
        'users_subtitle' => 'Registered workspace accounts',
        'roles_title' => 'Roles',
        'roles_subtitle' => 'Defined roles',
        'no_activity' => 'No activity recorded yet.',
        'no_notifications' => 'You have no new notifications.',
        'modules_body' => 'Penova business capabilities are added as <strong>Modules</strong> (e.g. Store, Booking, CRM, CMS, …). Each module registers its widgets with a simple descriptor from its own service provider and appears in this grid — without touching Core.',
    ],

    // Public landing page at "/" (RFC-005 / D-027 / D-AUDIT-006). Rendered in the
    // active locale via useI18n — English base, Persian when APP_LOCALE=fa — so the
    // page is locale-neutral, not Persian-hardcoded. 'Laravel Product Factory' (the
    // positioning tagline) and command snippets stay language-neutral by design.
    'welcome' => [
        'hero_intro' => 'A production-ready core for your Laravel products — authentication, users and roles, settings, notifications and a clean Workspace, ready to host your product modules.',
        'cta_workspace' => 'Go to the Workspace',
        'cta_docs' => 'View documentation',
        'features_heading' => 'What you get with Penova Core',
        'modules_heading' => 'Plug-in modules when you’re ready',
        'modules_intro' => 'Penova Core ships as a free foundation. Whenever you need a real product, you add product modules on top of this core — without rewriting everything.',
        'coming_soon' => 'Coming soon',
        'footer_docs' => 'Documentation',
        'features' => [
            'auth' => ['title' => 'Authentication & Accounts', 'desc' => 'Full login, registration and password reset flow, ready to drop into any product Workspace.'],
            'users' => ['title' => 'Users & Roles', 'desc' => 'Workspace screens to manage users, roles and permissions without any extra packages.'],
            'settings' => ['title' => 'Settings & Notifications', 'desc' => 'Runtime settings and a shared notification feed, so every module can reuse the same surface.'],
            'ui' => ['title' => 'Workspace UI & DataTable', 'desc' => 'A clean Workspace layout, reusable components and a server-side DataTable pattern for any CRUD page.'],
        ],
        'modules' => [
            'commerce' => ['title' => 'Commerce', 'desc' => 'Add selling as a module — products, cart, checkout and orders.'],
            'messaging' => ['title' => 'Messaging', 'desc' => 'SMS and transactional notifications through one module.'],
            'payments' => ['title' => 'Payments', 'desc' => 'Payment-gateway integration as an extensible module layer.'],
        ],
    ],
];
