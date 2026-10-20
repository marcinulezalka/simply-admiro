# Changelog

Format oparty na [Keep a Changelog](https://keepachangelog.com/pl/1.1.0/).

## [Unreleased]

### Added

- Opcja `--dry-run` w `vendor/bin/version-release`: pokazuje planowane wydanie (`Wersja x → y`) bez zmiany `CHANGELOG.md` i `VERSION`.
- Analiza statyczna PHPStan na poziomie `max` (`phpstan.neon`, `composer analyse`); Pint wymusza `declare(strict_types=1);`.
- `CLAUDE.md` z zasadami pakietu dla agentów AI.
- Wspólne skille AI dla Laravel Boost w `resources/boost/skills/`: `solid-dry`, `docs-after-tests`, `changelog-before-commit` (podgląd wydania przez `--dry-run`, wydanie tylko na wyraźne polecenie).

### Changed

- Licencja zmieniona z MIT na Simplysmart Restricted Source License 1.0 (pliki `LICENSE` i `THIRD_PARTY.md`), w `composer.json` `"license": "proprietary"`; użycie niekomercyjne bez ograniczeń, komercyjne po deklaracji w pliku `THIRD_PARTY.md` projektu (wzór wpisu w `THIRD_PARTY.md`), sprzedaż i komercyjna redystrybucja kodu jako produktu za pisemną zgodą autora.

### Fixed

- Liczbowy nagłówek sekcji w `[Unreleased]` (np. `### 2026`) kończy się czytelnym błędem „Nieznana sekcja” zamiast `TypeError`.

## [0.1.0] - 2026-10-19

### Added

- `vendor/bin/version-release`: wydanie wersji SemVer na podstawie sekcji `[Unreleased]` w `CHANGELOG.md` (Keep a Changelog) — podbija `VERSION` i zamienia `[Unreleased]` na datowaną sekcję wydania.
- Opcja `--root=<katalog>` wskazuje cel wydania (projekt albo pakiet); domyślnie bieżący katalog.
- Reguła podbicia: `Removed` lub wpis `**BREAKING**` → MAJOR (wymaga `--major-confirm`), `Added` / `Deprecated` → MINOR, `Changed` / `Fixed` / `Security` → PATCH.
- PHP ^8.4, autoload PSR-4 (`Simplysmart\Tools\` → `src/`), styl PER Coding Style (Laravel Pint, preset `per`).
- Data wydania w strefie `RELEASE_TIMEZONE` (domyślnie `Europe/Warsaw`); atomowy zapis plików z zachowaniem końców linii CRLF; walidacja pliku `VERSION`, sekcji i opcji.
