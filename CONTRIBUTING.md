# Contributing — admiro

Zasady rozwijania projektu i wydawania kolejnych wersji.

## Pakiety zewnętrzne

Zanim dodasz zależność do `composer.json`, sprawdź zasady w
`docs/zaleznosci.md` w Core (`simplysmart/mainboard`): licencja zgodna z MIT, `require` / `require-dev`, `composer audit`.

## Standardy kodu

PSR-4 (autoload: jedna klasa na plik, namespace odpowiada katalogowi) i styl PER Coding Style (następca PSR-12) pilnowany przez Laravel Pint z presetem `per` (`pint.json`). Każdy plik PHP zaczyna się od `declare(strict_types=1);` i nagłówka PHPDoc z prawami autorskimi (wymusza to Pint). Przed commitem uruchom `composer lint` i `composer analyse` (PHPStan, poziom `max`); `composer format` poprawia styl.

Kontrakty (interfejsy) paczki trzymamy w `src/Contracts/`, a ich implementacje wiążemy w `AdmiroServiceProvider`.

## Wydanie wersji

Wpisy zmian dodawaj w `CHANGELOG.md` pod `## [Unreleased]`, w sekcjach
`### Added`, `### Changed`, `### Deprecated`, `### Removed`, `### Fixed`, `### Security`.
Zmianę łamiącą oznacz na początku wpisu: `- **BREAKING** …`.

Pakiet nie ma własnego skryptu wydań — używa wspólnego przybornika
[`simplysmart/tools`](https://github.com/marcinulezalka/simply-tools) zainstalowanego w projekcie
głównym, w którym jest rozwijany (`packages/simplysmart/admiro`). Z katalogu projektu głównego:

```bash
vendor/bin/version-release --root=packages/simplysmart/admiro                  # PATCH / MINOR
vendor/bin/version-release --root=packages/simplysmart/admiro --major-confirm  # gdy wymagany MAJOR (Removed lub **BREAKING**)
```

Skrypt podbija `VERSION` i zamienia `[Unreleased]` na datowaną sekcję wydania. Regułę
podbijania (MAJOR / MINOR / PATCH) i strefę czasową daty opisuje README pakietu `simplysmart/tools`.
