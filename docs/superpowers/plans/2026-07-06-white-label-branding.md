# White Label / Branding Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Let a panel owner set brand name, logo URL, primary color, and footer text from Core Settings, and drive `AdminLayout` and the public `Welcome` page from those values, with Penova config defaults as fallback.

**Architecture:** Reuse the existing key-value `SettingsManager`; store the whole group under one JSON setting key `branding`. Add config defaults in `config/penova.php`, merge them under DB values in `HandleInertiaRequests` so every Inertia page receives a resolved `branding` prop. The Settings page edits raw DB values; `AdminLayout`/`Welcome` consume the resolved shared prop.

**Tech Stack:** Laravel 12, Inertia 2, Vue 3, Tailwind 4, Pest.

## Global Constraints

- UI is Persian / RTL (`<html dir="rtl">`); copy in the panel is Persian, brand field labels are English per the design.
- Use existing Core components (`TextInput`, `Button`, `Card`, `PageHeader`); `TextInput` exposes an `:error` string prop that renders inline.
- `primary_color` is **store-only** in v0.1 — persisted, exposed, editable, but does **not** re-theme the app. `bg-brand` `@theme` tokens stay static.
- Logo is a **URL** (validated `url`), not a file upload.
- Branding is stored as a **single JSON setting**, key `branding`, value `{ name, logo_url, primary_color, footer_text }`.
- Settings form binds to **raw DB values**; saving empty fields must not write config defaults as DB overrides.
- Commits: Conventional Commit subjects, **no AI attribution / Co-Authored-By trailers**.
- Frontend has no JS test runner; frontend tasks are verified by `npm run build` succeeding plus the backend Inertia assertions, with a final end-to-end manual verification task.

## File structure

| File | Responsibility | Change |
|------|----------------|--------|
| `config/penova.php` | Deploy-time branding defaults | Modify |
| `app/Http/Middleware/HandleInertiaRequests.php` | Resolve + share `branding` prop | Modify |
| `app/Core/Settings/Controllers/UpdateSettingsController.php` | Validate + persist branding | Modify |
| `resources/js/Core/Components/TextArea.vue` | Multiline input mirroring TextInput | Create |
| `resources/js/Core/Pages/Settings/Index.vue` | Branding editor card | Modify |
| `resources/js/Core/Layouts/AdminLayout.vue` | Logo + name + footer from branding | Modify |
| `resources/js/Core/Pages/Welcome.vue` | Logo + name + footer from branding | Modify |
| `tests/Feature/Core/Settings/BrandingTest.php` | Read-path + write-path feature tests | Create |

---

## Task 1: Branding config defaults + shared `branding` prop

**Files:**
- Modify: `config/penova.php` (after the `name` key, ~line 17)
- Modify: `app/Http/Middleware/HandleInertiaRequests.php`
- Test: `tests/Feature/Core/Settings/BrandingTest.php` (create)

**Interfaces:**
- Produces: `config('penova.branding')` → `array{name:string, logo_url:?string, primary_color:string, footer_text:string}`.
- Produces: Inertia shared prop `branding` (same shape), resolved as config defaults with non-empty DB `branding` values layered on top.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Core/Settings/BrandingTest.php`:

```php
<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('branding shared prop falls back to config defaults when nothing is saved', function () {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Core/Welcome')
            ->where('branding.name', config('penova.branding.name'))
            ->where('branding.primary_color', '#01696f')
            ->where('branding.footer_text', config('penova.branding.footer_text')));
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter="branding shared prop"`
Expected: FAIL — `branding` prop missing (`Property [branding] does not exist`) or config key null.

- [ ] **Step 3: Add the config defaults**

In `config/penova.php`, immediately after the `'name' => env('PENOVA_NAME', 'Penova'),` line, insert:

```php
    /*
    |--------------------------------------------------------------------------
    | Branding / White Label
    |--------------------------------------------------------------------------
    | Deploy-time defaults for the White Label surface. Admins override these
    | at runtime from Settings (stored under the single "branding" setting
    | key); the resolved values are shared with every Inertia page. Empty
    | runtime values fall back to these defaults.
    */
    'branding' => [
        'name' => env('PENOVA_BRAND_NAME', 'Penova Core Lite'),
        'logo_url' => env('PENOVA_BRAND_LOGO'),
        'primary_color' => env('PENOVA_BRAND_PRIMARY_COLOR', '#01696f'),
        'footer_text' => env('PENOVA_BRAND_FOOTER', 'Powered by Penova'),
    ],
```

- [ ] **Step 4: Share the resolved `branding` prop**

In `app/Http/Middleware/HandleInertiaRequests.php`, add the import near the other `use` statements at the top:

```php
use App\Core\Settings\Services\SettingsManager;
```

Then in `share()`, add a `branding` entry to the returned array, right after the `'app' => [...]` block:

```php
            // Resolved White Label branding: runtime settings (admin-owned)
            // layered over config/penova.php defaults, so every page — the
            // admin shell and the public welcome page — always gets complete
            // values, even before anything is saved. Empty DB values are
            // dropped so a blank field falls back to the config default
            // rather than overriding it with "".
            'branding' => array_merge(
                config('penova.branding'),
                array_filter(
                    app(SettingsManager::class)->get('branding', []),
                    fn ($value) => $value !== null && $value !== '',
                ),
            ),
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter="branding shared prop"`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add config/penova.php app/Http/Middleware/HandleInertiaRequests.php tests/Feature/Core/Settings/BrandingTest.php
git commit -m "feat(core): resolve and share White Label branding prop"
```

---

## Task 2: Persist + validate branding in the settings update

**Files:**
- Modify: `app/Core/Settings/Controllers/UpdateSettingsController.php`
- Test: `tests/Feature/Core/Settings/BrandingTest.php` (append)

**Interfaces:**
- Consumes: shared `branding` prop from Task 1 (to assert overrides take effect).
- Produces: `PUT /admin/settings` (route `penova.settings.update`) accepts `settings.branding.{name,logo_url,primary_color,footer_text}`, validates them, and persists the `branding` setting key as a JSON object. Non-branding keys (`site_name`, `contact_email`) keep saving as before.

> Note: the seeded admin from `PenovaCoreSeeder` holds `settings.manage`; if the PUT returns 403 in Step 2, that assumption is wrong — grant the permission the product-composition way before proceeding.

- [ ] **Step 1: Write the failing tests**

Append to `tests/Feature/Core/Settings/BrandingTest.php`:

```php
test('an owner can save branding and it overrides the config defaults', function () {
    $this->seed(\Database\Seeders\PenovaCoreSeeder::class);

    $this->post('/login', [
        'email' => config('penova.admin.email'),
        'password' => config('penova.admin.password'),
    ]);

    $this->put(route('penova.settings.update'), [
        'settings' => [
            'branding' => [
                'name' => 'Acme Store',
                'logo_url' => 'https://example.com/logo.png',
                'primary_color' => '#123456',
                'footer_text' => 'Powered by Acme',
            ],
        ],
    ])->assertRedirect();

    $this->assertDatabaseHas('settings', ['key' => 'branding']);

    // Fresh request (real requests are new processes; drop cached guards).
    $this->app['auth']->forgetGuards();

    $this->get('/')
        ->assertInertia(fn (Assert $page) => $page
            ->where('branding.name', 'Acme Store')
            ->where('branding.logo_url', 'https://example.com/logo.png'));
});

test('a blank branding field falls back to the config default, not an empty string', function () {
    $this->seed(\Database\Seeders\PenovaCoreSeeder::class);

    $this->post('/login', [
        'email' => config('penova.admin.email'),
        'password' => config('penova.admin.password'),
    ]);

    $this->put(route('penova.settings.update'), [
        'settings' => [
            'branding' => [
                'name' => '',
                'logo_url' => '',
                'primary_color' => '',
                'footer_text' => 'Powered by Acme',
            ],
        ],
    ])->assertRedirect();

    $this->app['auth']->forgetGuards();

    $this->get('/')
        ->assertInertia(fn (Assert $page) => $page
            ->where('branding.name', config('penova.branding.name'))
            ->where('branding.footer_text', 'Powered by Acme'));
});

test('an invalid logo url is rejected and nothing is saved', function () {
    $this->seed(\Database\Seeders\PenovaCoreSeeder::class);

    $this->post('/login', [
        'email' => config('penova.admin.email'),
        'password' => config('penova.admin.password'),
    ]);

    $this->put(route('penova.settings.update'), [
        'settings' => [
            'branding' => ['logo_url' => 'not-a-url'],
        ],
    ])->assertSessionHasErrors('settings.branding.logo_url');

    $this->assertDatabaseMissing('settings', ['key' => 'branding']);
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --filter="branding|logo url"`
Expected: the new tests FAIL — no validation on `logo_url` (invalid value saves), so `assertSessionHasErrors` / `assertDatabaseMissing` fail.

- [ ] **Step 3: Add validation + persist branding**

Replace the body of `__invoke()` in `app/Core/Settings/Controllers/UpdateSettingsController.php` with:

```php
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
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test --filter="branding|logo url"`
Expected: PASS (all branding tests green).

- [ ] **Step 5: Commit**

```bash
git add app/Core/Settings/Controllers/UpdateSettingsController.php tests/Feature/Core/Settings/BrandingTest.php
git commit -m "feat(core): validate and persist White Label branding settings"
```

---

## Task 3: Branding editor in the Settings page

**Files:**
- Create: `resources/js/Core/Components/TextArea.vue`
- Modify: `resources/js/Core/Pages/Settings/Index.vue`

**Interfaces:**
- Consumes: the `settings` page prop (`settings.branding` object when saved); the `PUT /admin/settings` payload shape from Task 2 (`settings.branding.*`).
- Produces: `TextArea` component with `label`, `rows`, `error`, `required` props and `v-model` (string), mirroring `TextInput`.

- [ ] **Step 1: Create the TextArea component**

Create `resources/js/Core/Components/TextArea.vue`:

```vue
<script setup>
/**
 * Core\UI — multiline text input with label and validation error, mirroring
 * TextInput. Works with Inertia's useForm:
 * <TextArea v-model="form.notes" :error="form.errors.notes" :rows="3" />
 */
defineProps({
    label: String,
    rows: { type: [String, Number], default: 3 },
    error: String,
    required: Boolean,
});

const model = defineModel({ type: String, default: '' });
</script>

<template>
    <div>
        <label v-if="label" class="mb-1 block text-sm font-medium text-slate-700">
            {{ label }}
        </label>

        <textarea
            v-model="model"
            :rows="rows"
            :required="required"
            class="block w-full rounded-md border-0 px-3 py-2 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-brand sm:text-sm"
            :class="{ 'ring-red-500': error }"
        />

        <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
    </div>
</template>
```

- [ ] **Step 2: Rewrite the Settings page with the branding card**

Replace the entire contents of `resources/js/Core/Pages/Settings/Index.vue` with:

```vue
<script setup>
/**
 * Core\Settings — generic key-value editor plus the White Label / Branding
 * group. Branding binds to raw DB values (empty when unset); config defaults
 * only surface at the display layer via the shared `branding` prop.
 */
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Core/Layouts/AdminLayout.vue';
import PageHeader from '@/Core/Components/PageHeader.vue';
import Card from '@/Core/Components/Card.vue';
import TextInput from '@/Core/Components/TextInput.vue';
import TextArea from '@/Core/Components/TextArea.vue';
import Button from '@/Core/Components/Button.vue';

const props = defineProps({ settings: Object });

const branding = props.settings.branding ?? {};

const form = useForm({
    settings: {
        site_name: props.settings.site_name ?? '',
        contact_email: props.settings.contact_email ?? '',
        ...props.settings,
        branding: {
            name: branding.name ?? '',
            logo_url: branding.logo_url ?? '',
            primary_color: branding.primary_color ?? '',
            footer_text: branding.footer_text ?? '',
        },
    },
});
</script>

<template>
    <AdminLayout title="تنظیمات">
        <PageHeader title="تنظیمات" subtitle="پیکربندی سایت، قابل ویرایش توسط مدیران" />

        <form class="max-w-3xl space-y-6" @submit.prevent="form.put('/admin/settings')">
            <Card>
                <div class="space-y-4">
                    <TextInput v-model="form.settings.site_name" label="نام سایت" />
                    <TextInput v-model="form.settings.contact_email" label="ایمیل تماس" type="email" />
                </div>
            </Card>

            <Card title="White Label / Branding">
                <p class="mb-4 text-sm text-slate-500">
                    نام برند و برندینگ Core Lite را برای پنل مدیریت و صفحهٔ خوش‌آمد تنظیم کنید.
                </p>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <TextInput
                        v-model="form.settings.branding.name"
                        label="Brand name"
                        :error="form.errors['settings.branding.name']"
                    />
                    <TextInput
                        v-model="form.settings.branding.logo_url"
                        label="Logo URL"
                        :error="form.errors['settings.branding.logo_url']"
                    />
                    <TextInput
                        v-model="form.settings.branding.primary_color"
                        label="Primary color (hex)"
                        :error="form.errors['settings.branding.primary_color']"
                    />
                    <div class="md:col-span-2">
                        <TextArea
                            v-model="form.settings.branding.footer_text"
                            label="Footer text"
                            :rows="2"
                            :error="form.errors['settings.branding.footer_text']"
                        />
                    </div>
                </div>
            </Card>

            <Button type="submit" :disabled="form.processing">ذخیره تنظیمات</Button>
        </form>
    </AdminLayout>
</template>
```

- [ ] **Step 3: Build to verify it compiles**

Run: `npm run build`
Expected: `✓ built` with no errors; a fresh `Settings-*.js` chunk emitted.

- [ ] **Step 4: Commit**

```bash
git add resources/js/Core/Components/TextArea.vue resources/js/Core/Pages/Settings/Index.vue
git commit -m "feat(core): branding editor card in settings page"
```

---

## Task 4: Consume branding in AdminLayout

**Files:**
- Modify: `resources/js/Core/Layouts/AdminLayout.vue`

**Interfaces:**
- Consumes: shared `branding` prop (`name`, `logo_url`, `footer_text`).

- [ ] **Step 1: Add the branding computed**

In `resources/js/Core/Layouts/AdminLayout.vue`, after the existing
`const unread = computed(() => page.props.unreadNotifications);` line, add:

```js
const branding = computed(() => page.props.branding ?? {});
```

- [ ] **Step 2: Replace the sidebar brand block**

Replace this block:

```html
            <div class="flex h-16 flex-col justify-center px-6">
                <div class="text-lg font-bold tracking-wide">Penova Core</div>
                <div class="text-xs text-slate-400">هستهٔ پنل محصولات شما</div>
            </div>
```

with:

```html
            <div class="flex h-16 items-center gap-2 px-6">
                <img
                    v-if="branding.logo_url"
                    :src="branding.logo_url"
                    alt=""
                    class="h-8 w-8 shrink-0 rounded"
                />
                <div class="min-w-0">
                    <div class="truncate text-lg font-bold tracking-wide">
                        {{ branding.name || 'Penova Core' }}
                    </div>
                    <div class="text-xs text-slate-400">هستهٔ پنل محصولات شما</div>
                </div>
            </div>
```

- [ ] **Step 3: Add the branded footer**

Replace this block (the end of the main column):

```html
            <main class="flex-1 p-6">
                <slot />
            </main>
        </div>
```

with:

```html
            <main class="flex-1 p-6">
                <slot />
            </main>

            <footer
                v-if="branding.footer_text"
                class="border-t border-slate-200 bg-white px-6 py-3 text-center text-xs text-slate-400"
            >
                {{ branding.footer_text }}
            </footer>
        </div>
```

- [ ] **Step 4: Build to verify it compiles**

Run: `npm run build`
Expected: `✓ built` with no errors.

- [ ] **Step 5: Commit**

```bash
git add resources/js/Core/Layouts/AdminLayout.vue
git commit -m "feat(core): drive AdminLayout brand + footer from branding prop"
```

---

## Task 5: Consume branding in the Welcome page

**Files:**
- Modify: `resources/js/Core/Pages/Welcome.vue`

**Interfaces:**
- Consumes: shared `branding` prop (`name`, `logo_url`, `footer_text`), with Penova defaults when empty.

- [ ] **Step 1: Add branding computeds**

In `resources/js/Core/Pages/Welcome.vue`, after the existing
`const user = computed(() => usePage().props.auth?.user);` line, add:

```js
const branding = computed(() => usePage().props.branding ?? {});
const logoUrl = computed(() => branding.value.logo_url || '/penova-logo.png');
const brandName = computed(() => branding.value.name || 'Penova Core Lite');
const footerText = computed(
    () => branding.value.footer_text || 'Penova Core Lite · Laravel Product Factory Starter',
);
```

- [ ] **Step 2: Bind the hero logo**

Replace:

```html
                <img
                    src="/penova-logo.png"
                    alt="Penova"
                    width="80"
                    height="80"
                    class="mx-auto h-20 w-20"
                />
```

with:

```html
                <img
                    :src="logoUrl"
                    :alt="brandName"
                    width="80"
                    height="80"
                    class="mx-auto h-20 w-20"
                />
```

- [ ] **Step 3: Bind the hero title**

Replace:

```html
                <h1 class="mt-6 text-4xl font-extrabold tracking-tight text-sand-900 sm:text-5xl">
                    Penova Core Lite
                </h1>
```

with:

```html
                <h1 class="mt-6 text-4xl font-extrabold tracking-tight text-sand-900 sm:text-5xl">
                    {{ brandName }}
                </h1>
```

- [ ] **Step 4: Bind the footer text**

Replace:

```html
                <p class="text-xs text-sand-600">Penova Core Lite · Laravel Product Factory Starter</p>
```

with:

```html
                <p class="text-xs text-sand-600">{{ footerText }}</p>
```

- [ ] **Step 5: Build to verify it compiles**

Run: `npm run build`
Expected: `✓ built` with no errors.

- [ ] **Step 6: Commit**

```bash
git add resources/js/Core/Pages/Welcome.vue
git commit -m "feat(core): drive Welcome hero + footer from branding prop"
```

---

## Task 6: End-to-end verification

**Files:** none (verification only).

- [ ] **Step 1: Run the full backend suite**

Run: `php artisan test`
Expected: all green — the new `BrandingTest` cases plus the existing `AdminFlowTest` (the AdminLayout/menu changes must not break it).

- [ ] **Step 2: Production build**

Run: `npm run build`
Expected: `✓ built`, fresh `Welcome-*`, `Settings-*`, and app chunks emitted.

- [ ] **Step 3: Manual acceptance (drive the app)**

Use the `run` / `verify` skill to launch the app, then confirm:
- `/` shows the bundled Penova logo, "Penova Core Lite" title, and default footer when no branding is saved.
- Log in, open `/admin/settings`, set Brand name (e.g. "Acme Store"), a Logo URL, and Footer text; save.
- The sidebar brand text/logo and the panel footer update; `/` reflects the new name, logo, and footer.
- Enter an invalid Logo URL → inline validation error, nothing persisted.

- [ ] **Step 4: Final commit (only if Step 3 required tweaks)**

```bash
git add -A
git commit -m "fix(core): branding QA adjustments"
```

---

## Self-review notes

- **Spec coverage:** config defaults (T1), SettingsManager reuse via single JSON key (T1/T2), generic-loop update + validation (T2), shared `branding` prop merge (T1), Settings branding card + TextArea (T3), AdminLayout name/logo/footer (T4), Welcome name/logo/footer (T5), default-behavior + tests (T1/T2/T6). All spec sections mapped.
- **primary_color:** stored/exposed/editable only; no theming — matches the approved decision.
- **Type consistency:** payload key `settings.branding.*` and error keys `form.errors['settings.branding.*']` match the controller rules; shared prop name `branding` consistent across middleware, AdminLayout, Welcome, and tests.
