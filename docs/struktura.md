# Struktura projektu

```
mainboard/
├── brand/               źródła marki: logo, sygnet, kolory (zob. brand/README.md)
├── config/mainboard.php konfiguracja Core (strefy, logowanie, bezpieczeństwo, dziennik, honeypot), tylko przez .env
├── database/
│   ├── migrations/      migracje wszystkich tabel Core i stref publicznych (zob. baza-danych.md)
│   └── seeders/permissions/   pliki ról stref publicznych (JSON, baza-danych.md §15)
├── lang/                tłumaczenia Core: lang/<locale>/<plik>.php (text_*, etykiety kolumn, e-maile)
├── public/              document root, punkt wejścia: public/index.php, assety (zob. niżej)
├── resources/views/     widoki Core: logowanie, konto, maile, komponenty, layouty, domyślna strona startowa (app/)
├── src/                 kod paczki, namespace Simplysmart\Mainboard\ (PSR-4)
│   ├── Contracts/       kontrakty (interfejsy) paczki — jedyne, od czego zależą inne pakiety i aplikacja
│   ├── Providers/       providery obszarów: strefy, logowanie, uprawnienia, dziennik zdarzeń, bezpieczeństwo
│   ├── Zones/           mechanizm stref: definicja, rejestr, registrary guardu, middleware i tras, sesja, strefa i konto żądania
│   ├── Auth/            logowanie kodem i PIN-em (SignIn/), zaufane urządzenia (Devices/), zmiana PIN-u, akcje na kontach, zdarzenia i listenery
│   ├── Permissions/     role i uprawnienia stref (spatie), pliki ról, nadawanie ról, członkostwa, właściciele stref
│   ├── Organizations/   zarządzanie organizacjami i dopisywanie członków
│   ├── Audit/           dziennik zdarzeń: mapowanie zdarzeń na wpisy, zapis do pliku i tabeli
│   ├── Security/        maskowanie błędów, pułapki na boty (Honeypot/), indeks e-maila, zaufane proxy
│   ├── Localization/    języki tras i interfejsu, formatowanie regionalne (local_*), ustawienia i słowniki lokalizacji
│   ├── Translation/     kaskada tłumaczeń (text_*) i ścieżka tłumaczeń frameworka
│   ├── Database/        kolumny standardowe, historia zmian, pule id, tabele stref, seedery referencyjne (Seeders/)
│   ├── Models/          modele danych (DataModel, OrganizationDataModel), konta stref, słowniki, uprawnienia; relacje w Relations/
│   ├── Queries/         odczyty dla paneli i usług (konta, organizacje, uprawnienia, słowniki, dziennik)
│   ├── Http/            middleware, kontrolery Core (logowanie, konto), trasy logowania, konta i pułapek
│   ├── Console/         komendy mainboard:* (seedy, synchronizacja ról, czyszczenie, honeypot)
│   ├── Mail/, Logging/, Listeners/, Enums/, Rules/, View/   e-maile, kanały logów, listenery, enumy, reguły walidacji, komponenty widoków
│   ├── helpers.php      helpery text_* i local_* (autoload "files")
│   └── MainboardServiceProvider.php   config, teksty, zasoby paczki i rejestracja providerów obszarów (auto-discovery)
├── docs/                dokumentacja
├── .claude/skills/      skille AI tylko dla domeny paczki (wspólne dostarcza simplysmart/admiro)
├── CHANGELOG.md         historia zmian (Keep a Changelog)
├── CONTRIBUTING.md      zasady rozwoju i wydawania wersji
├── VERSION              bieżąca wersja (SemVer), aktualizowana przez version-release z simplysmart/admiro (zob. CONTRIBUTING.md)
├── composer.json        metadane paczki simplysmart/mainboard, autoload PSR-4, extra.laravel.providers
├── phpstan.neon         konfiguracja PHPStan (poziom max)
├── CLAUDE.md            instrukcje dla agentów AI
├── LICENSE              licencja Simplysmart Restricted Source License 1.0
└── THIRD_PARTY.md       wzór deklaracji użycia komercyjnego
```

## Assety

Źródła marki (`brand/`) i assety serwowane przeglądarce (`public/`) są rozdzielone: `brand/`
trzyma wzorce w pełnej rozdzielczości, `public/` tylko zoptymalizowane pochodne.

```
public/
└── assets/
    ├── css/                     tokens.css → base.css → app.css
    ├── js/
    ├── img/
    │   ├── brand/               logo.svg (currentColor)
    │   └── ui/                  ikony i ilustracje interfejsu
    └── fonts/                   tylko woff2
```

Katalogi bez plików (`js/`, `img/ui/`, `fonts/`) zakładamy przy pierwszym pliku.

- **CSS bez bundlera.** Wartości marki (kolory, fonty, odstępy) są wyłącznie w `tokens.css`,
  komponenty używają zmiennych semantycznych (`--ss-color-text`), więc motyw ciemny zmienia się
  w jednym miejscu. Arkusze linkujemy osobno w `<head>`, bez `@import`.
- **Cache busting.** Adresy assetów dostają `?v=` z pliku `VERSION`, więc po wydaniu wersji
  przeglądarki pobierają nowe pliki.
- **Vite w paczce (koncepcja).** Docelowo paczka dostaje własny Vite z Tailwind 4, budowany ze
  szkieletu jednym `npm run build` (zob. [design-system.md](design-system.md#8-build-assetów-vite-w-każdej-paczce-uruchamiany-ze-szkieletu-docelowo)).
  Wtedy dochodzą:

  ```
  mainboard/
  ├── package.json         workspace npm @simplysmart/mainboard, skrypty dev i build
  ├── vite.config.js       build assetów paczki
  ├── resources/css|js/    źródła stylów i skryptów (Tailwind 4, @theme z tokenami)
  └── public/build/        wynik builda z manifest.json (nie jest commitowany)
  ```

## Co trafia na serwer

Tylko kod aplikacji i `public/`. `brand/`, `docs/` i `.claude/` mają w `.gitattributes` regułę `export-ignore`.
Szczegóły: [wdrozenie.md](wdrozenie.md).
