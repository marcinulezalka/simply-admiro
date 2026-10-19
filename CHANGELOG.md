# Changelog

Format oparty na [Keep a Changelog](https://keepachangelog.com/pl/1.1.0/).

## [Unreleased]

## [0.1.0] - 2026-10-19

### Added

- `vendor/bin/version-release`: wydanie wersji SemVer na podstawie sekcji `[Unreleased]` w `CHANGELOG.md` (Keep a Changelog) — podbija `VERSION` i zamienia `[Unreleased]` na datowaną sekcję wydania.
- Opcja `--root=<katalog>` wskazuje cel wydania (projekt albo pakiet); domyślnie bieżący katalog.
- Reguła podbicia: `Removed` lub wpis `**BREAKING**` → MAJOR (wymaga `--major-confirm`), `Added` / `Deprecated` → MINOR, `Changed` / `Fixed` / `Security` → PATCH.
- PHP ^8.4, autoload PSR-4 (`Simplysmart\Tools\` → `src/`), styl PER Coding Style (Laravel Pint, preset `per`).
- Data wydania w strefie `RELEASE_TIMEZONE` (domyślnie `Europe/Warsaw`); atomowy zapis plików z zachowaniem końców linii CRLF; walidacja pliku `VERSION`, sekcji i opcji.
