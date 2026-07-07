# Core → Strategy Alignment Audit

**Date:** 2026-07-07
**Scope:** `core` repository only (penova.ir and strategy handled elsewhere).
**Baseline:** tag `v0.1-baseline` (`3d4b1b7`).
**Method:** read-only. No code changed. Every finding routes to the governing
strategy document (per `strategy/README.md`). Severity: **High** (breaks a
binding rule or ships publicly), **Medium** (real drift, contained), **Low**
(cosmetic / comment-level).

> This audit measures the *implementation* against the *strategy*. Per
> Constitution Article 2, where they disagree the implementation is assumed
> wrong. Findings are a map, not a mandate — migration happens only after
> review, one governed change at a time.

---

## 0. Strengths (what already complies)

- **Core never depends on Modules.** `grep "App\Modules" app/Core` → zero hits.
  The load-bearing structural rule (13, rule 1) holds. *(13-architecture)*
- **Workspace rename is real and total.** Route `penova.workspace`, component
  `Core/Workspace/Index`, menu key `workspace`, UI label «میزکار»; no functional
  `penova.dashboard`/`Core/Dashboard` reference survives. *(06-glossary: Workspace)*
- **Manifest concept exists** as a public contract method + `ManifestRegistry`
  service, consumed generically via `config('penova.modules')`. *(06, 13)*
- **Platform Health** is used correctly — a diagnostic Ready/Warning status
  service, matching the glossary definition. *(06-glossary: Platform Health)*
- **Store is a Module, Core is business-free.** Booking/Crm removed; commerce
  lives in `app/Modules/Store`. *(03-product-principles)*

---

## 1. Violations (binding-rule breaches)

### V1 — "Core Lite" is used as the product's name — **High**
The glossary is explicit: Core is **"*Not* 'Core Lite,' 'starter,' or 'base
kit.'"** The term appears ~19× including public and default surfaces:
- `config/penova.php:57` — brand **default** `'Penova Core Lite'` (ships as the
  product name unless overridden).
- `resources/js/Core/Pages/Welcome.vue` — public landing page: title (`:70`),
  headings (`:126-127`), body copy (`:53-54,151`), footer `'… Laravel Product
  Factory Starter'` (`:18` — "Starter" is also forbidden).
- `resources/js/Core/Pages/Settings/Index.vue:48`, `InstalledModules.vue:5`,
  `WelcomeController.php:9`, `tests/Feature/Core/AdminFlowTest.php:5`,
  `app/Modules/Store/...` comments.
- **Governing:** `06-glossary.md` (Core term), `03-product-principles.md`
  (Core is complete, never a lite tier), `05-brand-guidelines.md` (public copy).

### V2 — The Manifest is under-realized (scattered hooks) — **High**
`06-glossary.md`: a Manifest is a Module's declaration of **"its menu entries,
widgets, permissions, and metadata … one Manifest,"** and `13` states *"the
Manifest is the model: one declaration a Module fulfills, not a dozen scattered
hooks."* Today `PenovaModule` exposes four separate statics:
`menu()` (`:69`), `widgets()` (`:76`), `permissions()` (`:88`), `manifest()`
(`:97`) — and `manifest()` carries **only** metadata (key/name/description/
version). The canonical Manifest should subsume menu/widgets/permissions/
metadata; instead it sits *alongside* the very hooks it is meant to replace.
- **Governing:** `06-glossary.md` (Manifest), `13-architecture-principles.md`
  (Small contracts). **This is a public-contract change → Tier 3 / RFC.**

---

## 2. Missing concepts (first-class strategy terms not built)

- **M1 — Operator.** The glossary's person-who-manages concept is **Operator**;
  the code has no such notion — it uses "admin" throughout (see D1). *(06)*
- **M2 — Command Center.** The keyboard-first universal action/navigation layer
  is a named first-class concept; it does not exist. (`QuickActions` is onboarding
  CTAs, not this.) *(06-glossary: Command Center)*
- **M3 — Platform Snapshot (the real one).** A point-in-time Platform state
  capture for backup/comparison/migration. Not built — and the name is currently
  mis-applied to a stats row (see D3). *(06)*
- **M4 — Resource as a formal contract.** Users/Roles are managed entities, but
  there is no explicit "Resource" abstraction/contract; it is informal. `11`
  lists "Resource contracts" as part of the public contract. *(06, 11)*
- **M5 — Marketplace.** Future/roadmap; legitimately absent now — noted so it is
  a conscious gap, not an oversight. *(06, 07-roadmap)*

---

## 3. Terminology drift (wrong word for an existing concept)

### D1 — "admin" for Operator / "admin panel" for Workspace — **Medium**
Glossary Never-use: *admin, user* (for Operator) and *admin panel* (for
Workspace). Pervasive: `AdminLayout.vue`, the `/admin` route prefix +
`penova.admin.*` config, "admin role/experience", `AdminFlowTest`, seeder
comments ("every admin gets full…"). The URL `/admin` may reasonably stay, but
the **concept naming** (admin → Operator) is drift. *(06-glossary)*

### D2 — "Dashboard" residual in the widget system — **Medium**
The dormant widget-grid keeps "dashboard" naming: `dashboardWidgets` shared prop,
`widgetAreas`, "the Lite dashboard is built…" and grid comments across
`PenovaCoreServiceProvider.php:50,55,89`, `HandleInertiaRequests.php:14,65,73`,
`PenovaModule.php:49,53,72`, `config/penova.php:119`. Glossary: the Dashboard is
at most one *view inside* the Workspace, never a system name. *(06)*

### D3 — "quick actions" and misused "Platform Snapshot" — **Medium**
- `resources/js/Core/Components/Workspace/QuickActions.vue` uses "quick actions",
  the exact term the glossary reserves *against* **Command Center**.
- `resources/js/Core/Components/Workspace/PlatformSnapshot.vue` names a small
  Users/Roles/Unread stats row "Platform Snapshot" — a **reserved** term for a
  state-capture subsystem. The widget squats on a first-class concept.
- **Governing:** `06-glossary.md` (Command Center; Platform Snapshot).

---

## 4. Architecture conflicts

### A1 — Manifest vs "small contracts" — **High** (= V2)
Four scattered statics instead of one declaration widens the Module→Core
contract surface. `13` wants the smallest, single declaration. *(13, 06)*

### A2 — A reserved concept implemented as a widget — **Medium**
"Platform Snapshot" (state capture for portability/backup) is a lifecycle
subsystem in the glossary; implementing it as a dashboard stat row both blocks
the real concept and mis-teaches the vocabulary. *(06, 13 platform-first)*

### A3 — Latent parallel system: the widget grid — **Low/Medium**
`03` anti-principle: *"we do not maintain parallel systems for the same
concept."* The widget-grid (`dashboardWidgets`/`widgetAreas`/`CORE_WIDGETS`) is
kept **dormant** for a future Pro surface. Acceptable as a deliberate, documented
hold — but it is an unowned parallel system if it lingers unnamed/undocumented.
*(03 anti-principles)*

---

## 5. Documentation gaps (vs 09)

- **G1 — The public contract is undocumented.** `11` enumerates the public
  contract (documented APIs, **Manifest**, Resource contracts, Workspace
  extension points, config format). None is marked/documented as the *stable
  public surface* vs internal. *(09, 11)*
- **G2 — Docs don't follow the 09 structure.** `09` mandates Getting started →
  Guides → Concepts → Reference → Modules. Core has `docs/architecture.md`,
  `docs/getting-started.md` but not the concept/reference/guides spine, and the
  Concepts vocabulary (Platform, Core, Workspace, Manifest, Resource) isn't a
  documented mental-model page. *(09)*
- **G3 — "Every example runs / is tested" is not enforced.** No CI check ties doc
  examples to tests. *(09)*
- **G4 — Drift leaks into docs.** "Core Lite"/"admin" terminology appears in
  prose and comments, so the docs teach the non-canonical words. *(09, 06)*

---

## 6. Migration order (cheapest-reversible first; contracts last)

Sequenced by governance tier and blast radius (`14`: the harder to reverse, the
more process). **Nothing here is executed without per-change approval.**

1. **Terminology sweep — internal only (Tier 1, low risk).** Rename "Core Lite"
   → "Core" and "Dashboard"/"admin" in **comments, tests, internal strings**
   where no public surface or contract changes. Fast, reversible, unblocks the
   rest. *(06)*
2. **Free the reserved names (Tier 1–2).** Rename the `PlatformSnapshot` widget
   (e.g. a neutral "at-a-glance"/stats name) and reconsider `QuickActions`
   naming, so `Platform Snapshot` and `Command Center` are available for their
   real concepts. UI-component-local. *(06)*
3. **Public copy — brand-coordinated (Tier 2).** "Core Lite" → "Core" in the
   Welcome page, Settings copy, and the `config/penova.php` brand **default**.
   Public wording → coordinate with `05-brand-guidelines` / `08-website-strategy`
   (and mirror on penova.ir). *(05, 08, 06)*
4. **Operator vocabulary (Tier 2–3).** Introduce the Operator concept and retire
   "admin" as the person/environment name (keep the `/admin` URL if desired).
   Larger surface; stage carefully. *(06)*
5. **Manifest unification (Tier 3 — RFC required).** Redesign the Module contract
   so the **Manifest** is the single declaration subsuming menu/widgets/
   permissions/metadata. This changes a public contract and a first-class
   concept → **RFC + Decision Log entry** before any code. *(13, 06, 14, 11)*
6. **Documentation (continuous).** Document the public contract, add the
   Concepts/vocabulary spine, and enforce tested examples — updated in the same
   change as each fix above (09: docs ship with the feature). *(09, 11)*

---

## Recommended immediate decision

Approve items **1–2** (safe, internal, reversible) to start; open an **RFC** for
item **5** (Manifest) since the constitution forbids changing a public contract
without one. Items 3–4 need brand/website coordination and are best staged after
the internal sweep. Order reflects `14-governance` (reversibility sets ceremony)
and `03` (protect Core's elegance; say no to protect the core).
