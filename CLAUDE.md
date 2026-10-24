# Package: simplysmart/admiro

## Context & Responsibility
Standalone Laravel Composer package (`laravel/framework` ^13): the **administrators' zone** (`admiro`) of simplysmart applications. It only configures the zone mechanism of Core `simplysmart/mainboard` (`docs/strefy.md` there): its own guard, accounts, session, URL slug and route-name prefix, plus the admin screens. Sign-in (e-mailed code + PIN), the zone tables and the security mechanisms come from Core. Developed inside the skeleton at `packages/simplysmart/admiro/` (symlinked into its `vendor/`), but it is its own Git repository and must work without the skeleton.

## Architectural Boundaries
- **Core first**: Depend on Core contracts (`Simplysmart\Mainboard\Contracts\*`) and its public base classes (`Zones\ZoneServiceProvider`, `Models\ZoneAccount`). Never reference skeleton classes (`App\...`) or other zone packages. A mechanism every zone could use (account creation `Console\CreateZoneAccountCommand`, the zone layout `x-mainboard::zone.layout`) goes to Core, not here; this package only configures it.
- **Contracts**: Own contracts, if any, live in `src/Contracts/` (`Simplysmart\Admiro\Contracts\*`), bound in `AdmiroServiceProvider`.
- **Service Provider**: `src/AdmiroServiceProvider.php` extends `ZoneServiceProvider`, auto-discovered via `extra.laravel.providers`. `register()` merges `config/admiro.php`; `boot()` lets Core register the zone, then loads views, translations and commands under the `admiro` namespace.
- **Zone identity**: The zone and guard name come from `config('admiro.zone.name')`; the slug from `ADMIRO_SLUG`; the route-name prefix `admiro.` is fixed in `routeNamePrefix()` and never configurable. Always name the guard (`$zone->get()->guard` from `CurrentZone`), never `Auth::user()`.
- **Routes**: Relative paths and short names in `routes/<tier>.php` (`account.php` = signed-in only; `zone.php` with `ZonePermission::middleware(Permission::...)`). Prefix, name prefix and middleware come from Core.
- **Admin panel**: controllers in `src/Http/Controllers/` (no queries there: Core queries `ZoneAccounts`, `ZoneRoleNames`, `ActiveOrganizations`), the signed-in admin and their permissions via `Http\AdminContext`, managed zone/account from the URL via `Http\ManagedAccounts` (unknown → 404). Account actions go only through Core `AccountAdministration` with `AccountActor`; roles only through `ZoneRoleAssignment` (operator organization); owners only through `ZoneOwnerProvisioners`. Acting on another admin needs `users.manage` plus `PermissionCoverage` (`Http\AdministratorGuard`); never on the own account. Organizations through Core `OrganizationManagement` and `OrganizationMembership` (members in `OrganizationMemberController`), settings through `LocalizationSettings`, dictionaries and VAT rates through `DictionaryManagement`, exchange rates through Core `ExchangeRateSync` / `ManualExchangeRates` (`ExchangeRateController`), the cookie policy through Core `CookiePolicies` (`CookiePolicyController`), days off through Core `HolidaySync` / `ManualHolidays` (`HolidayController`); every admin action is logged by `Http\AdminAudit` (Core `AdminActionPerformed`). The signed-in admin comes from `Http\AdminContext` (Core `CurrentAccount`). Skeleton tests extend `Tests\Feature\Admiro\AdmiroTestCase` (roles, `admin()`, small dictionaries).
- **Permissions**: `Enums\Permission` and `Enums\Role` mirror Core's roles file `database/seeders/permissions/admiro.json` (the only source of role assignments; the skeleton test `AdmiroPermissionsTest` keeps them equal). Labels are Core texts `permissions.admiro.*` / `roles.admiro.*`. Admins always sign in with the e-mailed code (trusted devices off unless `ADMIRO_TRUSTED_DEVICES=true`).
- **Database**: No migrations here. The `admiro_*` tables come from Core's migration `create_admiro_tables` (`ZoneTables`); `AdmiroAccount` only sets `$table`.
- **Navigation**: the zone menu is `Http\AdmiroNavigation` (each item only with the admin's permission, admin roles as the subtitle, no search), registered in `AdmiroServiceProvider` through Core's `Zones\ZoneNavigations`; panel views use design-system cards, tables, `x-mainboard::pagination` and the dictionaries tabs `dictionaries/tabs`.
- **Views & texts**: Views in `resources/views/` (`view('admiro::...')`, simple pages as `Route::view`), built on Core's `x-mainboard::zone.layout` (title, signed-in account, sign-out); texts in `lang/<locale>/` read via `text('admiro::...')`, falling back to Core texts. No inline scripts or styles (CSP). Never publish them into the skeleton; it overrides single files only.

## Development Rules
- **Strict Typing & header**: Every PHP file starts with `declare(strict_types=1);` and the PHPDoc copyright header (both enforced by Pint).
- **Coding Standards**: PHP ^8.4, PSR-4 (`Simplysmart\Admiro\` → `src/`), PER Coding Style via Laravel Pint `per` preset (`pint.json`).
- **Changelog**: Record user-visible changes in `CHANGELOG.md` under `## [Unreleased]` (Keep a Changelog; see `CONTRIBUTING.md`).

## Testing & Quality
Run in this directory before finishing (`composer install` first; Core comes from `../mainboard`):
- `composer lint` — style, strict types and header (`composer format` fixes them).
- `composer analyse` — PHPStan at level `max` (`phpstan.neon`: `src/`, `config/`, `routes/`). Fix the cause; no ignores or baseline.
- Intelephense — no diagnostics in changed files (see the skeleton's Quality Gates).
- No package test suite; integration tests live in the skeleton's `tests/Feature/Admiro/` (`composer test` in the skeleton root).

## Releasing
From the skeleton root: `vendor/bin/version-release --root=packages/simplysmart/admiro` (add `--dry-run` to preview without changing files). It really releases — never run it without `--dry-run` just to test.
