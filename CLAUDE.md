# Package: simplysmart/tools

## Context & Responsibility
Development toolbox (dev dependency, `require-dev`) for simplysmart projects and packages. Plain PHP ^8.4 CLI — no framework, no Laravel, no Artisan. Tools: `vendor/bin/version-release`, a SemVer release driven by the `## [Unreleased]` section of `CHANGELOG.md` (Keep a Changelog) that bumps `VERSION`. It is the single release implementation for the skeleton and every package — never copy it elsewhere. And `vendor/bin/composer-private`, which installs private packages from a git-ignored `private-packages.json` through `composer.private.json`/`.lock`, leaving the public `composer.json` untouched (see the skeleton's "Private Packages").

## Architectural Boundaries
- **Dev-only scope**: No application domain logic or business rules; code runs only in local/CI environments.
- **No runtime dependencies** beyond PHP: anything else goes to `require-dev` (tests, analysis, style).
- **Layout**: `bin/` holds thin entry points that only compose objects; `src/Console/` parses options and prints messages; `src/Release/` holds the release logic (planning, `CHANGELOG.md`/`VERSION` I/O, SemVer); `src/PrivatePackages/` holds the private-package installer (composer runs behind `ComposerRunner`); `resources/boost/skills/` holds the shared AI skills.

## Shared AI Skills
`resources/boost/skills/<name>/SKILL.md` is the single source of the ecosystem-wide skills: `solid-dry` (coding standards), `docs-after-tests` (documentation), `changelog-before-commit` (changelog and releases). Laravel Boost copies them into a host project's `.claude/skills/` (`boost.json` → `packages: ["simplysmart/tools"]`, then `php artisan boost:update`). Keep them project-agnostic: refer to `CLAUDE.md`, `composer lint` / `composer analyse` and `vendor/bin/version-release`, never to one project's paths. Format Markdown with a blank line after every heading (Boost reformats copies that way). New skills here cover CI/CD, versioning, changelog, release automation and shared code/documentation standards — not any package's business domain.

## Development Rules
- **Strict Typing**: Every PHP file starts with `declare(strict_types=1);` (enforced by Pint).
- **Coding Standards**: PHP ^8.4, PSR-4 (`Simplysmart\Tools\` → `src/`), PER Coding Style via Laravel Pint `per` preset (`pint.json`).
- **CLI tools**: Exit code `0` for success, `1` for error; errors to STDERR as clear Polish messages (`ReleaseException`); usage text in `ReleaseOptions::USAGE`.
- **Safety first**: Operations that write files must offer a preview. `version-release` has `--dry-run` (plans the release, writes nothing) and refuses a MAJOR bump without `--major-confirm`. File writes stay atomic (temporary files, then `rename()`).
- **Changelog & README**: Record user-visible changes in `CHANGELOG.md` under `## [Unreleased]`; keep option tables and examples in `README.md` in sync.

## Testing & Quality
Run in this directory before finishing:
- `composer test` — PHPUnit (`tests/`). Tests run the release only in temporary directories — never against a real project.
- `composer lint` — style and strict types (`composer format` fixes them).
- `composer analyse` — PHPStan at level `max` (`phpstan.neon`, covers `src/`, `bin/`, `tests/`). Fix the cause; no ignores or baseline.
