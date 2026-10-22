# Changelog

Format oparty na [Keep a Changelog](https://keepachangelog.com/pl/1.1.0/).

## [Unreleased]

### Added

- Role administratorów `guest`, `support`, `owner`, `hero` (enumy `Enums\Role` i `Enums\Permission`, przypisania w pliku ról Core `admiro.json`, zapis `php artisan mainboard:permissions:sync`); strefa działa w organizacji operatora.
- Administratorzy logują się zawsze kodem z e-maila i PIN-em: zaufane urządzenia z Core są w strefie wyłączone (`ADMIRO_TRUSTED_DEVICES=true` je włącza); zmiana PIN-u z Core pod `admiro.account.pin`.
- Strefa administratorów `admiro` na mechanizmie stref Core: własny guard, konta (`AdmiroAccount`, tabela `admiro_accounts`), sesja (`admiro_session`) i adres `/<ADMIRO_SLUG>` (domyślnie `/admiro`); logowanie kodem z e-maila i PIN-em z Core.
- Panel administratora `admiro.dashboard` dostępny tylko po zalogowaniu, na layoucie strefy z Core (zalogowane konto i wylogowanie); teksty `dashboard.*` (pl, en).
- Komenda `admiro:account:create {email} {name}` (na bazie `CreateZoneAccountCommand` z Core): zakłada aktywne konto administratora bez PIN-u (PIN ustawia administrator przy pierwszym logowaniu); odrzuca błędny adres i adres, który ma już konto.
- Repozytorium `https://github.com/marcinulezalka/simply-admiro` (adresy `homepage` i `support` w `composer.json`).
- Licencja Simplysmart Restricted Source License 1.0 (`LICENSE`, `THIRD_PARTY.md`), standardy kodu (PER przez Pint z nagłówkiem copyright, PHPStan `max`).
