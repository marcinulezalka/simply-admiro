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

Wspólny skrypt wydań projektu głównego i wszystkich paczek jest częścią tej paczki
(`bin/version-release`, `src/Tools/Release/`, opis w [docs/narzedzia.md](docs/narzedzia.md)). Z katalogu projektu
głównego, w którym paczka jest rozwijana (`packages/simplysmart/admiro`):

```bash
vendor/bin/version-release --root=packages/simplysmart/admiro                  # PATCH / MINOR
vendor/bin/version-release --root=packages/simplysmart/admiro --major-confirm  # gdy wymagany MAJOR (Removed lub **BREAKING**)
```

Skrypt podbija `VERSION` i zamienia `[Unreleased]` na datowaną sekcję wydania. Regułę
podbijania (MAJOR / MINOR / PATCH) i strefę czasową daty opisuje [docs/narzedzia.md](docs/narzedzia.md).

## Narzędzia deweloperskie

Kod w `src/Tools/` to czysty PHP CLI: bez Laravela, Core i klas strefy (uruchamia się przed frameworkiem). Operacje
zapisujące pliki albo działające na serwerze mają `--dry-run`, a `deploy:fresh` dodatkowo `--confirm=<DEPLOY_SSH>`.
Testy narzędzi są w `tests/Tools/` (`composer test`) i działają tylko w katalogach tymczasowych.
