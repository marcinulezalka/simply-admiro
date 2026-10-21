# Changelog

Format oparty na [Keep a Changelog](https://keepachangelog.com/pl/1.1.0/).

## [Unreleased]

### Added

- Strefa administratorów `admiro` na mechanizmie stref Core: własny guard, konta (`AdmiroAccount`, tabela `admiro_accounts`), sesja (`admiro_session`) i adres `/<ADMIRO_SLUG>` (domyślnie `/admiro`); logowanie kodem z e-maila i PIN-em z Core.
- Panel administratora `admiro.dashboard` dostępny tylko po zalogowaniu, z wylogowaniem; teksty `dashboard.*` (pl, en).
- Komenda `admiro:account:create {email} {name}`: zakłada aktywne konto administratora bez PIN-u (PIN ustawia administrator przy pierwszym logowaniu); odrzuca błędny adres i adres, który ma już konto.
- Licencja Simplysmart Restricted Source License 1.0 (`LICENSE`, `THIRD_PARTY.md`), standardy kodu (PER przez Pint z nagłówkiem copyright, PHPStan `max`).
