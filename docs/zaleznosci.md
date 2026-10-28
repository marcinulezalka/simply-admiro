# Pakiety zewnętrzne (third-party)

## Aktualne zależności

| Pakiet | Wersja | Licencja | Sekcja | Do czego |
| --- | --- | --- | --- | --- |
| `ext-bcmath` | `*` | PHP | `require` | przeliczanie kwot po kursach walut bez błędów zaokrągleń (`Queries\Dictionaries\ExchangeRatesOn::convert()`) — rozszerzenie PHP, nie pakiet |
| `laravel/framework` | `^13.0` | MIT | `require` | integracja z Laravelem: `ServiceProvider`, kontener, poczta i logi, tłumaczenia, oraz klasy dostępne tylko w pełnym frameworku: CSRF (`PreventRequestForgery`), `Vite` (nonce CSP), `TrustProxies` |
| `outhebox/blade-flags` | `^1.7` | MIT | `require` | flagi krajów i języków (`x-admiro::flag`, wariant flat = SVG z lipis/flag-icons, MIT); wariant domyślny (Twemoji, CC-BY-4.0) nieużywany; ciągnie `blade-ui-kit/blade-icons` (MIT) |
| `spatie/laravel-permission` | `^8.3` | MIT | `require` | role i uprawnienia stref z zespołami = organizacjami (`docs/baza-danych.md` §15); ciągnie `spatie/laravel-package-tools` (MIT) |
| `laravel/pint` | `^1.27` | MIT | `require-dev` | formatowanie kodu w stylu PER Coding Style i `declare(strict_types=1)` (`composer lint` / `composer format`) |
| `phpstan/phpstan` | `^2.1` | MIT | `require-dev` | analiza statyczna na poziomie `max` (`composer analyse`) |

Stan sprawdzisz poleceniem:

```bash
composer licenses   # pakiety z vendor/ i ich licencje
composer show -t    # drzewo zależności
```

## Zasady dodawania pakietu

1. **Tylko przez Composer.** Nie kopiuj cudzego kodu do repozytorium. Jeśli to konieczne,
   opisz go w sekcji [Kod skopiowany do repozytorium](#kod-skopiowany-do-repozytorium).
2. **Licencja permisywna.** Dozwolone są MIT, BSD-2/3-Clause, Apache-2.0 i ISC.
   Licencje copyleft (GPL, AGPL, LGPL) narzuciłyby swoje warunki użytkownikom paczki, więc wymagają
   osobnej decyzji.
3. **Właściwa sekcja.** `require` dla kodu potrzebnego w działaniu, `require-dev` dla narzędzi
   (testy, analiza statyczna, formatowanie).
4. **Zakres wersji z `^`** (np. `^11.0`). To paczka biblioteczna, więc `composer.lock` nie jest
   w repozytorium (`.gitignore`), a zbyt wąski zakres blokowałby projekty, które ją instalują.
5. **Bezpieczeństwo.** Po dodaniu lub aktualizacji uruchom `composer audit`.
6. **Dokumentacja.** Dopisz wiersz do tabeli powyżej i wpis w `CHANGELOG.md`: nowy pakiet w
   `### Added`, a zmiana wymaganej wersji w `### Changed`.

## Licencje i informacja o autorach

Pakiety instalowane przez Composer mają własne pliki licencji w `vendor/` i nie są częścią
repozytorium, więc nie trzeba dołączać ich treści.

## Kod skopiowany do repozytorium

| Co | Źródło | Autor | Licencja | Ścieżka |
| --- | --- | --- | --- | --- |
| ikony liniowe Core (rysunki SVG, nazwy plików Core) | [Lucide](https://lucide.dev) 1.2.140 (`@iconify-json/lucide`) | Lucide Icons and Contributors; część ikon z Feather (Cole Bemis) | ISC, ikony z Feather MIT | `public/assets/img/icons/*.svg`, treść licencji w `public/assets/img/icons/LICENSE-lucide.txt` |
| ikona wpisu domyślnego `home-dot` (rysunek SVG) | [Hugeicons](https://hugeicons.com) `hugeicons:home-06` (Iconify, `api.iconify.design`) | Hugeicons | MIT | `public/assets/img/icons/home-dot.svg`, treść licencji w `public/assets/img/icons/LICENSE-hugeicons.txt` |

Ikony mają wspólny nagłówek Core (`aria-hidden`, kreska 1,75, zaokrąglone końce i łączenia — także ikona z Hugeicons), a nazwa pliku Core
(np. `home`, `refresh`, `settings`) wskazuje odpowiednik z Lucide (`house`, `refresh-cw`, `sliders-horizontal`), więc widoki się nie zmieniają.

Każdy fragment cudzego kodu wklejony do projektu trzeba tu wpisać: źródło, autor,
licencja i ścieżka w projekcie. Plik musi też zachować oryginalną informację o prawach
autorskich.
