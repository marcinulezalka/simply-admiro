# Changelog

Format oparty na [Keep a Changelog](https://keepachangelog.com/pl/1.1.0/).

## [Unreleased]

### Added

- Menu strefy administratorów `Http\AdmiroNavigation` (pulpit; strefy i konta, właściciel strefy, administratorzy; klienci; dziennik, ustawienia, słowniki — każda pozycja tylko z uprawnieniem; role administratora pod nazwą konta) i widoki panelu na design systemie: pulpit z kartami-skrótami, strefy, konta strefy z wyszukiwaniem i stronicowaniem, administratorzy z rolami jako plakietkami, właściciel strefy, organizacje, członkowie z rolami strefy, dziennik zdarzeń z czytelnymi nazwami i filtrami, ustawienia z zakładkami słowników i przełącznikami języków, słowniki i stawki VAT.
- Nowy PIN administratora ma od 8 do 16 cyfr (minimum `ADMIRO_PIN_MIN_LENGTH`, domyślnie 8; maksimum z mainboard); istniejący krótszy PIN dalej działa przy logowaniu.
- Słowniki krajów, języków i walut oraz stawki VAT kraju w panelu (`settings.view` podgląd, `settings.manage` zmiany, w dzienniku zdarzeń); członkowie organizacji w osobnym `OrganizationMemberController`.
- Dziennik zdarzeń w panelu (`audit.view`): wpisy od najnowszych z filtrami strefy, zdarzenia i dat; akcje administratorów trafiają do dziennika Core (`AdminAudit` → `AdminActionPerformed`).
- Ustawienia systemu (`settings.view` podgląd, `settings.manage` zmiana): kraj, język i waluta domyślne oraz języki interfejsu.
- Organizacje w panelu: lista z organizacją nadrzędną i członkowie we wszystkich strefach (`organizations.view`, ma je też `support`), zakładanie, zmiany, włączanie i wyłączanie organizacji oraz usuwanie członków (`organizations.manage`, z ochroną innych administratorów); każda akcja w dzienniku (`AdminAudit`).
- Panel administratora: pulpit z sekcjami według uprawnień, strefy i ich konta (szukanie po adresie e-mail; `zones.view`), reset PIN-u, odblokowanie i wyłączenie konta strefy (nowe uprawnienie `accounts.manage`), zakładanie właścicieli stref przez kontrakt Core (`owners.assign`), administratorzy i ich role bez eskalacji (`users.manage`); konto innego administratora zmienia tylko ktoś z jego wszystkimi uprawnieniami, własnego konta nie zmienia się w panelu; teksty `admin.*` (pl, en).
- Pierwszy administrator z komendy: `admiro:account:create {email} {name} --role=hero`.
- Role administratorów `guest`, `support`, `owner`, `hero` (enumy `Enums\Role` i `Enums\Permission`, przypisania w pliku ról Core `admiro.json`, zapis `php artisan mainboard:permissions:sync`); strefa działa w organizacji operatora.
- Administratorzy logują się zawsze kodem z e-maila i PIN-em: zaufane urządzenia z Core są w strefie wyłączone (`ADMIRO_TRUSTED_DEVICES=true` je włącza); zmiana PIN-u z Core pod `admiro.account.pin`.
- Strefa administratorów `admiro` na mechanizmie stref Core: własny guard, konta (`AdmiroAccount`, tabela `admiro_accounts`), sesja (`admiro_session`) i adres `/<ADMIRO_SLUG>` (domyślnie `/admiro`); logowanie kodem z e-maila i PIN-em z Core.
- Panel administratora `admiro.dashboard` dostępny tylko po zalogowaniu, na layoucie strefy z Core (zalogowane konto i wylogowanie); teksty `dashboard.*` (pl, en).
- Komenda `admiro:account:create {email} {name}` (na bazie `CreateZoneAccountCommand` z Core): zakłada aktywne konto administratora bez PIN-u (PIN ustawia administrator przy pierwszym logowaniu); odrzuca błędny adres i adres, który ma już konto.
- Repozytorium `https://github.com/marcinulezalka/simply-admiro` (adresy `homepage` i `support` w `composer.json`).
- Licencja Simplysmart Restricted Source License 1.0 (`LICENSE`, `THIRD_PARTY.md`), standardy kodu (PER przez Pint z nagłówkiem copyright, PHPStan `max`).
