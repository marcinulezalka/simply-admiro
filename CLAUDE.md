# Package: simplysmart/tools

## Context & Responsibility
Development toolbox (dev dependency, `require-dev`) for simplysmart projects and packages. Plain PHP ^8.4 CLI — no framework, no Laravel, no Artisan. Currently one tool: `vendor/bin/version-release`, a SemVer release driven by the `## [Unreleased]` section of `CHANGELOG.md` (Keep a Changelog) that bumps `VERSION`. It is the single release implementation for the skeleton and every package — never copy it elsewhere.

## Architectural Boundaries
- **Dev-only scope**: No application domain logic or business rules; code runs only in local/CI environments.
- **No runtime dependencies** beyond PHP: anything else goes to `require-dev` (tests, analysis, style).
- **Layout**: `bin/` holds thin entry points that only compose objects; `src/Console/` parses options and prints messages; `src/Release/` holds the release logic (planning, `CHANGELOG.md`/`VERSION` I/O, SemVer).

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
