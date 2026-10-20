# Package: simplysmart/mainboard

## Context & Responsibility
Standalone Laravel Composer package (`laravel/framework` ^13): the **Application Core** of simplysmart applications — everything shared by the zones (contracts, base classes, DTOs, layouts and UI components, the shared localization middleware and the common zone mechanisms), kept out of the skeleton so it stays stock Laravel. Mainboard is not a zone: it has no users table, guard or zone slug of its own; zone packages build on its contracts. Developed inside the skeleton at `packages/simplysmart/mainboard/` (symlinked into its `vendor/`), but it is its own Git repository and must work without the skeleton.

## Architectural Boundaries
- **No application coupling**: Never reference skeleton classes (`App\...`) or other packages' concrete classes. Depend on contracts; receive dependencies through constructor injection or the container.
- **Contracts**: All contracts/interfaces of this package MUST reside in `src/Contracts/` (`Simplysmart\Mainboard\Contracts\*`). Bind their implementations in `MainboardServiceProvider::register()`.
- **Service Provider**: `src/MainboardServiceProvider.php`, auto-discovered via `extra.laravel.providers` in `composer.json`. Merge config in `register()` (`mergeConfigFrom`); load other resources in `boot()`: migrations (`loadMigrationsFrom`), views and Blade components (`loadViewsFrom`), routes. Keep these files inside the package, never in the skeleton.
- **Configuration**: Own settings live in `config/mainboard.php`, read only via `config('mainboard.key')`; `env()` only inside that file. Never publish it into the skeleton (override through `.env`). Extend framework config (e.g. log channels, mail options) with `config([...])` in the provider, never by editing the skeleton's `config/` (see the skeleton's "Framework Footprint").
- **Laravel-like layout and overrides**: Resources live in `resources/views/` and `lang/` and are registered with `loadViewsFrom(..., 'mainboard')` / `loadTranslationsFrom(..., 'mainboard')`; use them as `view('mainboard::...')` and `__('mainboard::...')`. The skeleton may override a single file in `resources/views/vendor/mainboard/` or `lang/vendor/mainboard/<locale>/` (it wins over the package, even when empty); write new views here, never in the skeleton, and never bulk-publish them (see the skeleton's "Framework Footprint"). Zone-specific views belong to their zone packages, not to Core. Framework translations without a namespace (`validation.*`, `auth.*`, `passwords.*`, `pagination.*`) live in `lang/<locale>/` and are inserted by `CoreTranslationPath` between the framework's and the application's `lang/` path, so the skeleton overrides them in `lang/<locale>/<file>.php`. `lang/` is also registered as the `mainboard` namespace for the `text_*` cascade (zone package → mainboard → skeleton → key, no fallback language; `Contracts\TextTranslator`, groups in `Translation\TextGroup`). In UI code use `text_*` / `local_*` (`src/helpers.php`), not `__()`; a new text family is a new `TextGroup` case plus one helper.
- **Assets**: Static CSS without a bundler in `public/assets/` (see `docs/struktura.md`); brand sources in `brand/`.
- **Design system**: UI follows `docs/design-system.md` (concept: Clean & Soft Enterprise, graphite accent, semantic `--ss-*` tokens only, no `dark:` classes in views).
- **Build (concept, `docs/design-system.md` §8)**: every package, Core included, gets its own `package.json` (npm workspace `@simplysmart/<package>`) and `vite.config.js` (Tailwind 4) building into its own `public/build/`. The skeleton runs them all with one `npm run dev|build` through the orchestrator from `simplysmart/tools`; never add a package to the skeleton's `vite.config.js` or `package.json`. Served via `php artisan mainboard:assets` (`public/vendor/<package>`), loaded with `x-mainboard::assets`, not `@vite`.

## Security & Multi-Zone Isolation
Zones are isolated sandboxes (own users table, guard and session). The skeleton's `CLAUDE.md` section "Multi-Zone Isolation" is the source of truth. Core provides the shared mechanisms; zone packages configure them. In short:
- **Isolated guards**: Core code never assumes a guard: it receives the zone's guard (from the zone's config or a contract) and always names it (`Auth::guard($guard)`, `auth:<zone-guard>`). Never rely on the default `Auth::user()` or `auth.defaults.guard`.
- **Session isolation**: Core's session middleware sets the zone's cookie name (and path/domain where zones differ) before `StartSession`, so concurrent logins in different zones keep working.
- **Zone mechanism** (`docs/strefy.md`): zone packages extend `Zones\ZoneServiceProvider`; Core registers the guard, the `zone.<zone>` middleware group (replaces `web`) and the routes per access tier (`public.php`, `account.php`, `zone.php`). Read the request's zone from `Contracts\CurrentZone`, never from static state.
- **Cache isolation**: Build zone cache keys only with `Zones\ZoneCacheKey`. Prefix cache keys with the zone (`zone:<zone>:...`) while the store is `database`; use `Cache::tags(['zone:<zone>', ...])` only on `redis` (`database` and `file` stores do not support tags). Never call a global `Cache::flush()`.

## Database & Models
`docs/baza-danych.md` is the source of truth (concept stage: mechanisms are not implemented yet). In short:
- **One place**: migrations and seeders of every table (Core and zones) live in this package's `database/`, loaded by `MainboardServiceProvider`. Production blocks destructive DB commands; demo seeders refuse to run there.
- **Schema**: `baseColumns()` → domain columns → `closingColumns()` (Blueprint macros); `status` is a string backed by a PHP enum, flags are `boolean()` + `'boolean'` cast; every table gets `history_<table>` written by MySQL triggers.
- **Models**: extend `Models\MainModel`; no business logic and no `Auth` in models, no queries in controllers (`Queries/`, `Actions/`); every column has a cast and a `text_data` label; relations only in traits; public URLs use `token`, never `id`.

## Routing & URL Conventions
The skeleton's `CLAUDE.md` section "Routing & URL Conventions" is the source of truth; in short:
- **Clean signatures**: Controllers, actions and Form Requests never accept `$locale` or a zone slug; use `App::getLocale()`.
- **Shared localization**: Core owns the single localization middleware (`App::setLocale()`, `URL::defaults()`, `forgetParameter('locale')`) and the locale-mode logic (one locale → no prefix; several → `{locale}` always). Zone packages only attach it.
- **Core routes**: Any route of Core itself (e.g. `/` redirecting to the default locale) is registered by `MainboardServiceProvider` from relative route files with the fixed name prefix `mainboard.`; zone slugs and zone route-name prefixes belong to the zone packages.
- **Named routes only**: Generate links with `route()`, never hand-built URLs containing the locale or slug.

## AI Skills
`.claude/skills/` holds only skills for this package's own domain (e.g. domain actions, DTOs, package-specific UI components). Shared skills — `solid-dry`, `docs-after-tests`, `changelog-before-commit` — come from `simplysmart/tools` (`resources/boost/skills/`) and are installed into the skeleton by Laravel Boost; edit them there, never copy them here.

## Development Rules
- **Strict Typing**: Every PHP file starts with `declare(strict_types=1);` (enforced by Pint).
- **Coding Standards**: PHP ^8.4, PSR-4 (`Simplysmart\Mainboard\` → `src/`), PER Coding Style via Laravel Pint `per` preset (`pint.json`).
- **Dependencies**: Follow `docs/zaleznosci.md` (MIT-compatible licences, `^` ranges, update the table and `CHANGELOG.md`). `composer.lock` is not committed.
- **Changelog**: Record user-visible changes in `CHANGELOG.md` under `## [Unreleased]` (Keep a Changelog; see `CONTRIBUTING.md`).

## Testing & Quality
Run in this directory before finishing:
- `composer lint` — style and strict types (`composer format` fixes them).
- `composer analyse` — PHPStan at level `max` (`phpstan.neon`). Fix the cause; no ignores or baseline.
- No package test suite yet; integration with the skeleton is covered by the skeleton's `tests/Feature/PackageIntegrationTest.php` (`composer test` in the skeleton root).

## Releasing
From the skeleton root: `vendor/bin/version-release --root=packages/simplysmart/mainboard` (add `--dry-run` to preview without changing files). It really releases — never run it without `--dry-run` just to test.
