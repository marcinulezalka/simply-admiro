# admiro

Strefa administratorów aplikacji simplysmart: odizolowany panel z własnym guardem, kontami, sesją i adresem URL.

Paczka Composer: `simplysmart/admiro`, namespace `Simplysmart\Admiro\`. Mechanizm stref, logowanie kodem z e-maila i PIN-em oraz tabele dostarcza Core [`simplysmart/mainboard`](https://github.com/marcinulezalka/simply-mainboard) (`docs/strefy.md`).

## Wymagania

- PHP ^8.4, Laravel 13
- `simplysmart/mainboard`; provider `Simplysmart\Admiro\AdmiroServiceProvider` rejestruje się sam (auto-discovery)

## Instalacja

```bash
composer require simplysmart/admiro
php artisan migrate                                                            # tabele admiro_* z migracji Core
php artisan mainboard:organizations:seed && php artisan mainboard:permissions:sync  # organizacja operatora i role
php artisan admiro:account:create ewa@example.com "Ewa Kowalska" --role=hero    # pierwszy administrator
```

Strefa nie ma rejestracji: konta zakłada tylko komenda `admiro:account:create {email} {name} [--role=…]`. Konto jest aktywne i nie ma PIN-u — administrator ustawia go sam przy pierwszym logowaniu (kod z e-maila, potem PIN). Komenda odrzuca błędny adres, adres, który ma już konto, i nieznaną rolę (wtedy konto nie powstaje).

Pierwszy administrator dostaje rolę z komendy (`--role=hero`), kolejnym role nadaje się w panelu.

## Panel

| Sekcja | Trasa | Uprawnienie |
| --- | --- | --- |
| Pulpit z odnośnikami do dostępnych sekcji | `admiro.dashboard` | zalogowany administrator |
| Strefy z kontami i konta strefy (szukanie po dokładnym adresie e-mail) | `admiro.zones.index`, `admiro.zones.accounts` | `zones.view` |
| Reset PIN-u, odblokowanie, wyłączenie konta strefy | `admiro.zones.accounts.action` | `accounts.manage` |
| Organizacje (firmy i oddziały) i ich członkowie we wszystkich strefach | `admiro.organizations.index`, `admiro.organizations.members` | `organizations.view` |
| Zakładanie, zmiany, włączanie i wyłączanie organizacji, usuwanie członków | `admiro.organizations.*` | `organizations.manage` |
| Założenie właściciela strefy (strefy z `ZoneOwnerProvisioner` w Core) | `admiro.owners.create`, `admiro.owners.store` | `owners.assign` |
| Administratorzy i ich role | `admiro.administrators.index`, `admiro.administrators.roles.*` | `users.manage` |
| Dziennik zdarzeń z filtrami (strefa, zdarzenie, daty) | `admiro.audit.index` | `audit.view` |
| Ustawienia: kraj, język i waluta domyślne, języki interfejsu (w granicach `APP_LOCALES`) | `admiro.settings.show` / `admiro.settings.update` | `settings.view` / `settings.manage` |

- Organizacji operatora nie da się wyłączyć ani przenieść, a drzewo organizacji nie dopuszcza cykli.
- Konto innego administratora (także usunięcie go z organizacji operatora) zmienia tylko ten, kto ma `users.manage` i wszystkie jego uprawnienia; roli nie nada się szerszej niż własne uprawnienia (np. `owner` nie nada `hero`). Własnego konta i własnych ról nie zmienia się w panelu.
- Każda akcja trafia do dziennika zdarzeń Core z aktorem (administrator i strefa), widocznego w sekcji „Dziennik zdarzeń”.

## Strefa

| Element | Wartość |
| --- | --- |
| Nazwa strefy, guard | `admiro` |
| Adres | `/<ADMIRO_SLUG>` (domyślnie `/admiro`; przy kilku językach `/{locale}/admiro`) |
| Trasy | `admiro.dashboard` (panel, tylko po zalogowaniu), `admiro.auth.*` (logowanie z Core), `admiro.account.pin` (zmiana PIN-u z Core) |
| Tabele | `admiro_accounts`, `admiro_login_challenges`, `admiro_sessions`, `admiro_trusted_devices` |
| Logowanie | zawsze kod z e-maila i PIN; zaufane urządzenia wyłączone (`ADMIRO_TRUSTED_DEVICES=true` je włącza) |
| Ciasteczko sesji | `admiro_session` |

Zmiana `ADMIRO_SLUG` zmienia tylko adres; nazwy tras zostają `admiro.*`. Slug nie może być kodem języka.

Role administratorów (`Enums\Role`): `guest` (bez uprawnień), `support` (podgląd stref, kont i dziennika), `owner` (wszystko poza
zarządzaniem superadministratorami i konserwacją systemu) i `hero` (wszystko). Uprawnienia są w
`Enums\Permission`, a przypisania w pliku ról Core `database/seeders/permissions/admiro.json`, zapisywanym
komendą `php artisan mainboard:permissions:sync`. Strefa działa w organizacji operatora aplikacji.

## Nadpisywanie

Teksty (`lang/{pl,en}/dashboard.php`) aplikacja zmienia plikiem `lang/vendor/admiro/<locale>/dashboard.php`, a widoki plikiem w `resources/views/vendor/admiro/`. Config — tylko przez `.env`.

## Rozwój

```bash
composer install   # zależności paczki; Core z ../mainboard (repozytorium typu path)
composer lint      # styl, strict types, nagłówek copyright
composer format    # poprawia powyższe
composer analyse   # PHPStan, poziom max
```

Testy integracji są w projekcie głównym (`tests/Feature/Admiro/`).

## Licencja

Simplysmart Restricted Source License 1.0 — © 2026 Marcin Ulezalka. Do celów niekomercyjnych
kod możesz bezpłatnie przeglądać, kopiować, uruchamiać i modyfikować. Użycie komercyjne (także
w projektach dla klientów) jest dozwolone po wpisaniu deklaracji do pliku `THIRD_PARTY.md` w
Twoim projekcie — wzór wpisu jest w [THIRD_PARTY.md](THIRD_PARTY.md). Sprzedaż,
sublicencjonowanie i komercyjna redystrybucja kodu jako produktu wymagają pisemnej zgody autora.
Kopie muszą zachować informację o prawach autorskich i treść licencji. Wiążąca jest angielska
treść pliku [LICENSE](LICENSE).
