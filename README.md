# admiro

Rdzeń aplikacji simplysmart w jednej paczce: baza danych (migracje wszystkich tabel), mechanizm stref, logowanie kodem z e-maila i PIN-em, uprawnienia i organizacje, słowniki lokalizacji, design system i układy UI — razem ze strefą administratorów (panel) i przybornikiem deweloperskim (wydania, paczki prywatne, wdrożenie).

Paczka Composer: `simplysmart/admiro`, namespace `Simplysmart\Admiro\`. Do wersji 0.3.0 rdzeń był osobną paczką `simplysmart/admiro` (historia w [CHANGELOG.md](CHANGELOG.md)).

## Wymagania

- PHP ^8.4, Laravel 13
- Providery `Simplysmart\Admiro\AdmiroServiceProvider` (rdzeń) i `Simplysmart\Admiro\Panel\PanelServiceProvider` (strefa administratorów) rejestrują się same (auto-discovery)
- Pakiety zewnętrzne: zob. [docs/zaleznosci.md](docs/zaleznosci.md)

## Instalacja

```bash
composer require simplysmart/admiro
php artisan migrate                                                            # tabele rdzenia i stref
php artisan admiro:organizations:seed && php artisan admiro:permissions:sync   # organizacja operatora i role
php artisan admiro:account:create ewa@example.com "Ewa Kowalska" --role=hero   # pierwszy administrator
```

Strefa administratorów nie ma rejestracji: konta zakłada tylko komenda `admiro:account:create {email} {name} [--role=…]`. Konto jest aktywne i nie ma PIN-u — administrator ustawia go sam przy pierwszym logowaniu (kod z e-maila, potem PIN). Komenda odrzuca błędny adres, adres, który ma już konto, i nieznaną rolę (wtedy konto nie powstaje). Pierwszy administrator dostaje rolę z komendy (`--role=hero`), kolejnym role nadaje się w panelu.

## Tłumaczenia i formatowanie

Helpery dla kodu i widoków Blade (`{{ text_action('save') }}`). Teksty szukane są kaskadowo:
**paczka strefy → admiro → `lang/` aplikacji**, tylko w bieżącym języku. Gdy klucza nie ma
nigdzie, helper zwraca sam klucz (np. `actions.save`), a nie tekst w innym języku, więc od razu
widać brak tłumaczenia.

| Helper | Klucz | Przykład |
| --- | --- | --- |
| `text($key)` | dowolny, z namespace paczki lub bez | `text('zone::dashboard.title')` |
| `text_action($key)` | `actions.<key>` | `text_action('save')` → „Zapisz” |
| `text_data($model, $column)` | `data.<model>.<kolumna>`, potem `data.<kolumna>` | `text_data($user, 'first_name')` |
| `text_file($key)` | `files.<key>` (eksport CSV, Excel, PDF) | `text_file('headers.email')` |
| `text_email($key)` | `emails.<key>` | `text_email('welcome.subject', ['name' => $name])` |
| `local_money($kwota, ?$waluta)` | waluta domyślna: `APP_CURRENCY` (PLN) | `1 234,50 zł` |
| `local_number($liczba, $precyzja)` | | `9 876 543,21` |
| `local_date($data, ?$format)` | format ISO Carbona, domyślnie `L` | `20.10.2026`, `D MMMM YYYY` → „20 października 2026” |
| `local_day($dzień)` / `local_month($miesiąc)` | dzień ISO 1–7 / miesiąc 1–12 albo data | „poniedziałek”, „marzec” |

Namespace paczki przechodzi przez grupę: `text_email('zone::welcome.subject')` szuka
`zone::emails.welcome.subject`, potem `admiro::emails.welcome.subject`, potem `emails.welcome.subject`.
Teksty paczki (np. `lang/pl/actions.php`) aplikacja zmienia plikiem `lang/vendor/admiro/<locale>/<plik>.php`,
a teksty paczki strefy plikiem `lang/vendor/<paczka>/<locale>/<plik>.php`; widoki — plikiem w
`resources/views/vendor/admiro/`. Config — tylko przez `.env`. Kontrakty: `Contracts\TextTranslator` i `Contracts\LocalFormatter`.

## Strefa administratorów

| Element | Wartość |
| --- | --- |
| Nazwa strefy, guard | `admiro` |
| Adres | `/<ADMIRO_SLUG>` (domyślnie `/admiro`; przy kilku językach `/{locale}/admiro`) |
| Trasy | `admiro.dashboard` (panel, tylko po zalogowaniu), `admiro.auth.*` (logowanie), `admiro.account.pin` (zmiana PIN-u) |
| Tabele | `admiro_accounts`, `admiro_login_challenges`, `admiro_sessions`, `admiro_trusted_devices` |
| Logowanie | zawsze kod z e-maila i PIN; zaufane urządzenia wyłączone (`ADMIRO_TRUSTED_DEVICES=true` je włącza) |
| PIN | od 8 do 16 cyfr (minimum `ADMIRO_PIN_MIN_LENGTH`, domyślnie 8; maksimum 16 z ustawień logowania rdzenia) |
| Ciasteczko sesji | `admiro_session` |
| Dokumenty prawne | polityka prywatności i RODO (`legal_documents`, strony `admiro.legal.*`), edycja w ustawieniach panelu: `admiro.legal-documents.index` |

Zmiana `ADMIRO_SLUG` zmienia tylko adres; nazwy tras zostają `admiro.*`. Slug nie może być kodem języka.

Role administratorów (`Panel\Enums\Role`): `guest` (bez uprawnień), `support` (podgląd stref, kont i dziennika), `owner` (wszystko poza
zarządzaniem superadministratorami i konserwacją systemu) i `hero` (wszystko). Uprawnienia są w
`Panel\Enums\Permission`, a przypisania w pliku ról `database/seeders/permissions/admiro.json`, zapisywanym
komendą `php artisan admiro:permissions:sync`. Strefa działa w organizacji operatora aplikacji.

### Panel

| Sekcja | Trasa | Uprawnienie |
| --- | --- | --- |
| Pulpit z odnośnikami do dostępnych sekcji | `admiro.dashboard` | zalogowany administrator |
| Strefy z kontami i konta strefy (szukanie po dokładnym adresie e-mail) | `admiro.zones.index`, `admiro.zones.accounts` | `zones.view` |
| Reset PIN-u, odblokowanie, wyłączenie konta strefy | `admiro.zones.accounts.action` | `accounts.manage` |
| Organizacje (firmy i oddziały) i ich członkowie we wszystkich strefach | `admiro.organizations.index`, `admiro.organizations.members` | `organizations.view` |
| Zakładanie, zmiany, włączanie i wyłączanie organizacji, usuwanie członków | `admiro.organizations.*` | `organizations.manage` |
| Założenie właściciela strefy (strefy z `ZoneOwnerProvisioner`) | `admiro.owners.create`, `admiro.owners.store` | `owners.assign` |
| Administratorzy i ich role | `admiro.administrators.index`, `admiro.administrators.roles.*` | `users.manage` |
| Dziennik zdarzeń z filtrami (strefa, zdarzenie, daty) | `admiro.audit.index` | `audit.view` |
| Ustawienia: kraj, język i waluta domyślne, języki interfejsu (w granicach `APP_LOCALES`) | `admiro.settings.show` / `admiro.settings.update` | `settings.view` / `settings.manage` |
| Słowniki krajów, języków i walut (włączanie i wyłączanie wpisów) | `admiro.dictionaries.index` / `admiro.dictionaries.toggle` | `settings.view` / `settings.manage` |
| Stawki VAT kraju (nowa stawka od daty, zakończenie stawki) | `admiro.tax-rates.index` / `admiro.tax-rates.store`, `admiro.tax-rates.end` | `settings.view` / `settings.manage` |

- Organizacji operatora nie da się wyłączyć ani przenieść, a drzewo organizacji nie dopuszcza cykli.
- Konto innego administratora (także usunięcie go z organizacji operatora) zmienia tylko ten, kto ma `users.manage` i wszystkie jego uprawnienia; roli nie nada się szerszej niż własne uprawnienia (np. `owner` nie nada `hero`). Własnego konta i własnych ról nie zmienia się w panelu.
- Każda akcja trafia do dziennika zdarzeń z aktorem (administrator i strefa), widocznego w sekcji „Dziennik zdarzeń”.

## Narzędzia deweloperskie

admiro dostarcza też przybornik deweloperski dla projektu głównego i wszystkich paczek (dawniej `simplysmart/tools`):
`vendor/bin/simplysmart` z poleceniami `release`, `private:install`, `deploy:lock`, `deploy`, `deploy:onboarding`
i `deploy:fresh` (wdrożenie od zera: `migrate:fresh`, wymaga `--confirm=<DEPLOY_SSH>`), a także
`vendor/bin/version-release` i `vendor/bin/composer-private`. Do tego wspólne skille AI dla Laravel Boost. Opis opcji
i konfiguracji: [docs/narzedzia.md](docs/narzedzia.md).

```bash
vendor/bin/version-release --dry-run                       # podgląd wydania projektu
vendor/bin/simplysmart deploy --dry-run                    # plan wdrożenia
vendor/bin/simplysmart deploy:fresh --confirm=<DEPLOY_SSH> # wdrożenie od zera (usuwa wszystkie dane na serwerze)
```

## Dokumentacja

| Dokument | Zawartość |
| --- | --- |
| [Struktura projektu](docs/struktura.md) | katalogi i ich rola |
| [Strefy](docs/strefy.md) | mechanizm stref, logowanie, izolacja |
| [Wdrożenie](docs/wdrozenie.md) | hosting współdzielony: co wysyłać, a czego nie |
| [Baza danych i modele](docs/baza-danych.md) | koncepcja schematu, modeli, historii i tłumaczeń danych |
| [Design system](docs/design-system.md) | koncepcja wyglądu interfejsu, tokenów i komponentów |
| [Narzędzia deweloperskie](docs/narzedzia.md) | wydania, paczki prywatne, wdrożenie |
| [Pakiety zewnętrzne](docs/zaleznosci.md) | zależności Composer, licencje, zasady dodawania |
| [Marka](brand/README.md) | logo admiro i marka simplysmart, kolory, zasady użycia |
| [CONTRIBUTING.md](CONTRIBUTING.md) | zasady rozwoju i wydawania wersji |
| [CHANGELOG.md](CHANGELOG.md) | historia zmian |

Pełny spis: [docs/README.md](docs/README.md).

## Rozwój

```bash
composer install   # zależności paczki
composer test      # PHPUnit: narzędzia deweloperskie (tests/Tools/)
composer lint      # styl, strict types, nagłówek copyright
composer format    # poprawia powyższe
composer analyse   # PHPStan, poziom max
```

Testy integracji rdzenia i strefy są w projekcie głównym (`tests/Feature/`).

## Licencja

Simplysmart Restricted Source License 1.0 — © 2026 Marcin Ulezalka. Do celów niekomercyjnych
kod możesz bezpłatnie przeglądać, kopiować, uruchamiać i modyfikować. Użycie komercyjne (także
w projektach dla klientów) jest dozwolone po wpisaniu deklaracji do pliku `THIRD_PARTY.md` w
Twoim projekcie — wzór wpisu jest w [THIRD_PARTY.md](THIRD_PARTY.md). Sprzedaż,
sublicencjonowanie i komercyjna redystrybucja kodu jako produktu wymagają pisemnej zgody autora.
Kopie muszą zachować informację o prawach autorskich i treść licencji. Wiążąca jest angielska
treść pliku [LICENSE](LICENSE).
