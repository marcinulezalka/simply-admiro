# simplysmart/tools

Przybornik deweloperski dla projektów i pakietów simplysmart. Bez zależności poza PHP ^8.4,
bez frameworka.

## Instalacja

```bash
composer require --dev simplysmart/tools
```

## Wydanie wersji

Projekt i każdy pakiet mają własne `CHANGELOG.md` ([Keep a Changelog](https://keepachangelog.com/pl/1.1.0/))
i `VERSION` (`MAJOR.MINOR.PATCH`). Skrypt czyta wpisy z `## [Unreleased]`, ustala podbicie,
zapisuje nową wersję do `VERSION` i zamienia `[Unreleased]` na datowaną sekcję wydania.

```bash
vendor/bin/version-release                                        # bieżący katalog
vendor/bin/version-release --root=packages/simplysmart/mainboard  # inny projekt lub pakiet
vendor/bin/version-release --major-confirm                        # gdy wymagany MAJOR
```

| Sekcja w `[Unreleased]` | Podbicie |
| --- | --- |
| `Removed` lub wpis zaczynający się od `**BREAKING**` / `BREAKING:` | MAJOR (wymaga `--major-confirm`) |
| `Added`, `Deprecated` | MINOR |
| `Changed`, `Fixed`, `Security` | PATCH |

Wygrywa najwyższe podbicie wśród niepustych sekcji. Inne nagłówki i wpisy bez nagłówka
`### <sekcja>` są odrzucane. Datę wydania liczy strefa `RELEASE_TIMEZONE`
(domyślnie `Europe/Warsaw`).

## Rozwój

PSR-4 (autoload: jedna klasa na plik, namespace odpowiada katalogowi) i styl PER Coding Style (następca PSR-12) pilnowany przez Laravel Pint z presetem `per` (`pint.json`).

```bash
composer install
composer test     # PHPUnit
composer lint     # sprawdzenie stylu
composer format   # poprawienie stylu
```

## Licencja

MIT — zob. [LICENSE](LICENSE).
