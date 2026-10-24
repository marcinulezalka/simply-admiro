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
vendor/bin/version-release --dry-run                              # podgląd: pokazuje wersję, nie zmienia plików
```

| Sekcja w `[Unreleased]` | Podbicie |
| --- | --- |
| `Removed` lub wpis zaczynający się od `**BREAKING**` / `BREAKING:` | MAJOR (wymaga `--major-confirm`) |
| `Added`, `Deprecated` | MINOR |
| `Changed`, `Fixed`, `Security` | PATCH |

Wygrywa najwyższe podbicie wśród niepustych sekcji. Inne nagłówki i wpisy bez nagłówka
`### <sekcja>` są odrzucane. Datę wydania liczy strefa `RELEASE_TIMEZONE`
(domyślnie `Europe/Warsaw`).

## Paczki prywatne

`vendor/bin/composer-private` instaluje paczki z prywatnych repozytoriów tak, żeby publiczny `composer.json` i `composer.lock` projektu ich nie wymieniały. Lista leży w `private-packages.json` w katalogu projektu (dodaj go do `.gitignore`):

```json
{
    "require": {
        "<vendor>/<paczka>": "@dev"
    }
}
```

Narzędzie zapisuje `composer.private.json` (= `composer.json` z dopisanymi paczkami), przy pierwszym uruchomieniu kopiuje `composer.lock` jako `composer.private.lock` (wersje publicznych paczek zostają) i uruchamia `composer update <paczki>` z `COMPOSER=composer.private.json`. Oba pliki dodaj do `.gitignore`.

```bash
vendor/bin/composer-private              # bieżący katalog
vendor/bin/composer-private --dry-run    # pokazuje paczki; nie zapisuje plików i nie uruchamia composera
vendor/bin/composer-private --root=<katalog>
```

Bez `private-packages.json` kończy się kodem 0 i nic nie robi (publiczny klon, CI). Zwykłe `composer install` / `update` usuwa paczki prywatne z `vendor/`, więc potem trzeba uruchomić narzędzie ponownie. W projekcie wygodnie podpiąć je jako skrypt: `"private:install": "@php vendor/bin/composer-private"`.

### Wdrożenie: paczki z repozytoriów git

Lokalnie paczki z repozytoriów typu `path` (np. `packages/*/*`) są dowiązaniami, a `composer.lock` zapisuje je jako „z katalogu”. Na serwerze tych katalogów nie ma, więc tryb `--deploy` buduje osobny manifest wdrożeniowy:

- `composer.deploy.json` — `composer.json` bez repozytoriów `path`, z repozytorium VCS (adres `git remote origin`) każdej paczki z tych katalogów, którą projekt wymaga (`require`, `require-dev`, paczki prywatne), i z dopisanymi paczkami prywatnymi;
- `composer.deploy.lock` — kopia `composer.private.lock` (albo `composer.lock`) zaktualizowana przez `composer update <paczki> --no-install --no-scripts`: paczki przypięte do commitów z repozytoriów zdalnych, `vendor/` bez zmian.

```bash
vendor/bin/composer-private --deploy             # po wypchnięciu repozytoriów paczek
vendor/bin/composer-private --deploy --dry-run   # pokazuje paczki; nie zapisuje plików
```

Paczka z niewypchniętymi commitami dostaje ostrzeżenie (lock wskaże kod z repozytorium zdalnego), paczka bez `origin` to błąd. Oba pliki dodaj do `.gitignore` i wyślij na serwer, a tam: `COMPOSER=composer.deploy.json composer install --no-dev`. Do repozytoriów prywatnych composer potrzebuje tokenu (`composer config -g github-oauth.github.com <token>`). W projekcie: `"deploy:lock": "@php vendor/bin/composer-private --deploy"`.

## Skille AI

Pakiet dostarcza wspólne skille dla agentów AI w `resources/boost/skills/`: `solid-dry` (SOLID, DRY, standardy PHP 8.4), `docs-after-tests` (dokumentacja po testach) i `changelog-before-commit` (CHANGELOG i wydania z `--dry-run`). W projekcie Laravel z [Laravel Boost](https://laravel.com/docs/boost) dopisz pakiet do `boost.json`, a Boost skopiuje skille do `.claude/skills/`:

```json
{ "packages": ["simplysmart/tools"] }
```

```bash
php artisan boost:update
```

## Rozwój

PSR-4 (autoload: jedna klasa na plik, namespace odpowiada katalogowi) i styl PER Coding Style (następca PSR-12) pilnowany przez Laravel Pint z presetem `per` (`pint.json`), który wymaga też `declare(strict_types=1);` w każdym pliku PHP.

```bash
composer install
composer test      # PHPUnit
composer lint      # sprawdzenie stylu i strict types
composer format    # poprawienie stylu
composer analyse   # PHPStan, poziom max
```

## Licencja

Simplysmart Restricted Source License 1.0 — © 2026 Marcin Ulezalka. Do celów niekomercyjnych
kod możesz bezpłatnie przeglądać, kopiować, uruchamiać i modyfikować. Użycie komercyjne (także
w projektach dla klientów) jest dozwolone po wpisaniu deklaracji do pliku `THIRD_PARTY.md` w
Twoim projekcie — wzór wpisu jest w [THIRD_PARTY.md](THIRD_PARTY.md). Sprzedaż,
sublicencjonowanie i komercyjna redystrybucja kodu jako produktu wymagają pisemnej zgody autora.
Kopie muszą zachować informację o prawach autorskich i treść licencji. Wiążąca jest angielska
treść pliku [LICENSE](LICENSE).
