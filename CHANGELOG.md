# Changelog

Format oparty na [Keep a Changelog](https://keepachangelog.com/pl/1.1.0/).

## [Unreleased]

### Added

- Organizacje i uprawnienia stref (`docs/baza-danych.md` §15): zależność `spatie/laravel-permission` ^8.3 z zespołami = organizacjami (`organization_id`), skonfigurowana przez Core (`Permissions\PermissionDefaults`, wspólny klucz cache `zone:shared:spatie.permission`); tabela `organizations` i model `Models\Organization` (drzewo, pełny standard), organizacja operatora aplikacji z komendy `mainboard:organizations:seed`; modele `Models\Permissions\{Role,Permission}` z pełnym standardem tabel danych (token, checksum, soft delete, historia, pula id), tabele łączące spatie z historią; odmowy uprawnień `permission_denials` (`PermissionDenial`), które wygrywają z rolą; `ZoneAccount` z rolami spatie w guardzie strefy (`HasZonePermissions`, kontrakt `HasZoneRoles`); kontrakt `ZonePermissionChecker` z adapterem `SpatiePermissionChecker`; bez bieżącej organizacji przypisania są odrzucane, a sprawdzenia dają brak dostępu; etykiety kolumn (pl, en).
- Fundament bazy danych (`docs/baza-danych.md`): kolumny standardowe `Database\Columns` (`base()` z flagami `is_active` i `is_default`, `closing()` z globalnymi `json_default_settings` i `json_tags`, nazwy w `ColumnNames`), `Models\DataModel` (token, soft delete, casty, checksum rekordu, scope'y `active()` i `default()`, odczyt wartości domyślnych `defaultSetting()`); pule id `Database\IdSlots` (1–999 sloty seederów i providerów, rekordy użytkowników od 1000), tłumaczenia `json_i18n` (`HasI18n::translatedName()` bez języka zapasowego), etykiety kolumn `lang/{pl,en}/data.php` i kontrakt `HasLabel`.
- Historia zmian każdej tabeli danych w `<tabela>_history` zapisywana triggerami (`Database\History`: `create`, `sync`, `drop`, `trackedColumns`; dialekty MySQL i SQLite), z aktorem zmian (strefa, konto z guarda strefy, id żądania) ustawianym przez middleware `RecordHistoryActor` i dla zadań kolejki.
- Słowniki lokalizacji: tabele `locale_currencies`, `locale_languages`, `locale_countries`, `locale_country_language`, `locale_tax_rates` z historią `<tabela>_history`, kody ISO w kolumnach `alpha_code_2`, `alpha_code_3`, `numeric_code` (`CodeColumns`, kontrakt `DictionaryEntry`), dane terytorialne krajów z ICU (kontynent `Enums\Continent`, region UN M49, ludność) i listy krajów pogrupowane kontynentami, modele `Models\Dictionaries\{Currency,Language,Country,CountryLanguage,TaxRate}`, relacje w `Models\Relations`, enum `TaxRateType` (etykiety pl, en), odczyty `DefaultEntries` (kraj, język, waluta aplikacji) i `TaxRatesInForce` (stawki w danym dniu); config `mainboard.localization` (`APP_COUNTRY`, `APP_DEFAULT_VAT`); kraje i języki mają kod ikony flagi `flag_icon` (zestaw flag-icons, język → flaga kraju).
- Komponent `x-mainboard::flag` (flaga kraju lub języka z `flag_icon` jako dekoracyjny inline SVG 4:3, styl `.ss-flag`; nieznany kod → neutralna flaga „xx”) i zależność `outhebox/blade-flags` ^1.7 (SVG z lipis/flag-icons, MIT); po wdrożeniu `php artisan icons:cache`.
- Komenda `mainboard:localization:seed`: waluty, języki i kraje z ICU (nazwy w językach interfejsu, numery kierunkowe, UE, waluty i języki urzędowe krajów) oraz stawki VAT państw UE (stan na 2025 r.); idempotentna, zachowuje wybory administratora.
- Języki interfejsu ze słownika (`Contracts\InterfaceLocales`, `locale_languages.is_interface` w granicach `APP_LOCALES`): język wyłączony w słowniku przekierowuje na ten sam adres w języku domyślnym.
- Domyślna strona startowa aplikacji `welcome` (`resources/views/app/`, dodane przez provider jako lokalizacja widoków bez przestrzeni nazw, więc plik aplikacji ją nadpisuje): jedna strona bez akcji — logo simplysmart w panelu marki z odwróconymi kolorami motywu, opis Core i jego możliwości (teksty `welcome.*`, pl, en), style `.ss-welcome`; komponenty `x-mainboard::logo` (logo SVG inline) i `x-mainboard::styles` (style Core inline z nonce CSP, `View\CoreAssets`), slot `head` w `x-mainboard::page`.
- Konta stref z pełnym standardem tabel danych: `ZoneTables` buduje `<strefa>_accounts` na `Columns::base()`/`closing()` (soft delete, checksum, `json_default_settings`, `json_tags`), z historią `<strefa>_accounts_history` i pulą id od 1000; `MainAuthenticatable` ma traity `DataModel`; `AccountLocator::isEmailTaken()` — adres usuniętego konta zostaje zajęty, `CreateZoneAccount` go odrzuca.
- Migracja `add_standard_columns_to_users_table`: stockowa tabela `users` aplikacji dostaje kolumny standardowe (w kolejności standardu, `token` nullable i uzupełniony dla istniejących wierszy), historię `users_history` i pulę id; bez tabeli `users` nic nie robi.
- Szablon bazowy stron `x-mainboard::page` z meta `title`, `description`, `keywords`, `tags` i `robots`; layouty logowania i strefy składają się z niego.
- Baza komendy `Console\CreateZoneAccountCommand` (`<strefa>:account:create {email} {name}`: walidacja, strefa, komunikaty) i layout strefy `x-mainboard::zone.layout` (tytuł, zalogowane konto, wylogowanie; tekst `sign_in.signed_in_as`, pl, en).
- Zakładanie kont stref bez rejestracji: akcja `Auth\CreateZoneAccount` (aktywne konto bez PIN-u, PIN ustawia właściciel przy pierwszym logowaniu; zajęty adres → `AccountAlreadyExists`) i `ZoneDefinition::accountModel()`.
- Migracja `create_admiro_tables`: tabele strefy administratorów `admiro` (paczka `simplysmart/admiro`).
- Logowanie stref kodem z e-maila i PIN-em (etap 2, `docs/strefy.md`): trasy `<strefa>.auth.*`, wymienne kroki `Contracts\SignInStep` z kolejnością w `auth.steps` (domyślnie kod, ustawienie PIN-u, PIN), bez przeskakiwania kroków i z czasem ważności każdego kroku; wylogowanie tylko z bieżącej strefy.
- Zabezpieczenia logowania: ta sama ścieżka i ten sam czas odpowiedzi dla każdego adresu (szyfrowany job `IssueSignInCode`), jeden ogólny komunikat porażki, kod tylko jako HMAC (ważny najnowszy, TTL, limit prób, odstęp wysyłek), PIN z regułą `StrongPin` i aktualizacją hasha, blokady 15 min → 1 h → 24 h z licznikiem zerowanym dopiero po udanym logowaniu i mailem do właściciela, limity per IP, per adres + IP i per adres z dowolnego IP, regeneracja sesji, epoka konta (`auth_epoch`) unieważniająca wszystkie sesje, wylogowanie kont nieaktywnych i zablokowanych, powrót do logowania po wygaśnięciu formularza (419).
- Schemat tabel strefy `Database\ZoneTables` (`<strefa>_accounts` z szyfrowanym e-mailem i `email_hash`, `<strefa>_login_challenges`, `<strefa>_sessions`), modele `MainModel` (klucz trasy `token`), `MainAuthenticatable` i `ZoneAccount`, kontrakty konta (`SignInAccount`, `HasPinCredential`, `LockableAccount`, `RevocableAccess`), `AccountLocator`, `AccountStore`, `LoginChallengeRepository`, `BlindIndex`.
- Zdarzenia `SignInSucceeded`, `SignInRejected`, `AccountLocked`, `SignedOut`; dziennik `sign_in` (`AUTH_LOG_CHANNEL`, bez adresów e-mail), komenda `mainboard:auth:prune` planowana co godzinę; teksty `sign_in.*` i e-maile `login_code`, `account_locked` (pl, en); `DB::prohibitDestructiveCommands()` na produkcji, migracje i widoki Core ładowane przez provider.
- Osobny magazyn sesji każdej strefy (`ZoneSessionManager`): sesje stref nie mieszają się z sesją aplikacji ani ze sobą.
- Mechanizm stref (etap 1, `docs/strefy.md`): rejestr `Contracts\ZoneRegistry`, strefa żądania `Contracts\CurrentZone` (`scoped`), `Zones\ZoneServiceProvider` dla paczek stref, registrary guardu, grupy middleware `zone.<strefa>` i tras (prefiks `{locale}`/slug, stały prefiks nazw, poziomy dostępu `AccessTier` według plików `public.php`/`account.php`/`zone.php`), klucze cache `ZoneCacheKey`.
- Sesja per strefa (`UseZone`): ciasteczko `<strefa>_session`, poza `local` zawsze `secure`; strefa z logowaniem: tabela `<strefa>_sessions`, 30 min (`ZONE_SESSION_LIFETIME`), `SameSite=Strict`, szyfrowanie, wygaśnięcie po zamknięciu przeglądarki; po odpowiedzi stan aplikacji jest przywracany.
- Wspólny middleware języka `SetLocale` i `LocaleRouting` (`APP_LOCALES`: jeden język bez prefiksu, kilka z `{locale}`).
- `SecurityHeaders`: `X-Frame-Options`, `nosniff`, `Referrer-Policy`, `Permissions-Policy`, COOP, HSTS na HTTPS (`SECURITY_HSTS`), CSP z nonce dla Vite (`SECURITY_CSP`); w strefach z logowaniem `no-store` i `X-Robots-Tag`.
- CSRF bez współdzielonego ciasteczka `XSRF-TOKEN` (`PreventRequestForgeryWithoutCookie`), przekierowania auth/guest do tras bieżącej strefy (`ZoneRedirects`, bez globalnej trasy `login`), zaufane proxy z `TRUSTED_PROXIES`.
- `CONTRIBUTING.md` z zasadami wpisów w `CHANGELOG.md` i wydawania wersji.
- Licencja Simplysmart Restricted Source License 1.0: pliki `LICENSE` i `THIRD_PARTY.md`, sekcja „Licencja” w README, w `composer.json` `"license": "proprietary"`; użycie niekomercyjne bez ograniczeń, komercyjne po deklaracji w pliku `THIRD_PARTY.md` projektu (wzór wpisu w `THIRD_PARTY.md`), sprzedaż i komercyjna redystrybucja kodu jako produktu za pisemną zgodą autora.
- Koncepcja bazy danych i modeli w `docs/baza-danych.md`: migracje i seedery wszystkich tabel w Core, makra pól, historia `history_*` na triggerach, `MainModel`, słowniki i tłumaczenia danych, RODO, tokeny w URL, checksum, mappery.
- Koncepcja design systemu w `docs/design-system.md`: styl Clean & Soft Enterprise, tokeny semantyczne z motywem ciemnym, grafitowy akcent, karty i warstwy layoutu, komponenty Blade Core; docelowy build: własny Vite z Tailwind 4 w każdej paczce, uruchamiany ze szkieletu jednym `npm run dev|build` (npm workspaces i orkiestrator z `simplysmart/tools`), serwowany przez `mainboard:assets` (opis wdrożenia w `docs/wdrozenie.md`).
- Dokumentacja w `docs/`: struktura projektu, wdrożenie na hosting współdzielony oraz pakiety zewnętrzne (aktualne zależności, licencje, zasady dodawania).
- `composer.json` paczki `simplysmart/mainboard` (PHP ^8.4, autoload PSR-4: `Simplysmart\Mainboard\` → `src/`), `.gitignore` wykluczający pliki środowiska lokalnego (`.env*`, cały katalog `infrastructure/` z konfiguracją vhosta, PHP-FPM i SSL, katalogi IDE), `.editorconfig` i `.gitattributes`.
- Integracja z Laravelem 13: zależność `illuminate/support` ^13.0 i `MainboardServiceProvider` rejestrowany automatycznie (`extra.laravel.providers`); kontrakty paczki w `src/Contracts/` (`Simplysmart\Mainboard\Contracts\`).
- Analiza statyczna PHPStan na poziomie `max` (`phpstan.neon`, `composer analyse`); Pint wymusza `declare(strict_types=1);`.
- `CLAUDE.md` z zasadami paczki dla agentów AI: granice zależności, kontrakty, Service Provider, bramki jakości.
- Katalog `.claude/skills/` wyłącznie na skille domenowe paczki (wspólne skille — standardy kodu, dokumentacja, changelog i wydania — dostarcza `simplysmart/tools` przez Laravel Boost); `.gitignore` pomija tylko lokalne pliki sesji AI: `.claude/settings.local.json` i `CLAUDE.local.md`.
- Wpis w `### Changed` oznaczony `**BREAKING**` (lub `BREAKING:`) na początku linii wymusza podbicie MAJOR w `tools/version-release.php`.
- Identyfikacja marki w `brand/`: logo (SVG i PNG na jasne i ciemne tło), sygnet „S” w stylu kroju Mole (PNG z przezroczystym tłem, na jasne i ciemne tło), kolory i zasady użycia w `brand/README.md`; katalog wykluczony z paczek (`export-ignore`).
- Struktura assetów w `public/assets/` (CSS, grafiki, fonty) i style oparte na tokenach marki w `tokens.css`, z motywem ciemnym.
- Konfiguracja Core w `config/mainboard.php` (`mergeConfigFrom`), nadpisywana zmiennymi `MAIL_LOG_CHANNEL`, `LOG_DAILY_DAYS`, `MAIL_REQUIRE_TLS`.
- Dziennik poczty przeniesiony ze szkieletu do Core: dzienny kanał `mail` (`storage/logs/email-RRRR-MM-DD.log`), mailer `log` kierowany do niego, wymuszony STARTTLS dla SMTP — każde ustawienie tylko wtedy, gdy aplikacja go nie zdefiniowała; listener `LogSentMail` odnotowuje wysyłki przez prawdziwy transport.
- Zależności `illuminate/log` ^13.0 i `illuminate/mail` ^13.0.
- Polskie tłumaczenia frameworka w `lang/pl/` (`validation`, `auth`, `passwords`, `pagination`), ładowane przez Core między tłumaczeniami frameworka a `lang/` aplikacji, więc plik `lang/pl/<plik>.php` w aplikacji nadal je nadpisuje; zależność `illuminate/translation` ^13.0.
- Kaskadowe tłumaczenia bez języka zapasowego (paczka strefy → mainboard → `lang/` aplikacji → klucz): kontrakt `TextTranslator` i helpery `text`, `text_action`, `text_data`, `text_file`, `text_email`; akcje interfejsu w `lang/{pl,en}/actions.php`.
- Formatowanie regionalne: kontrakt `LocalFormatter` i helpery `local_money`, `local_number`, `local_date`, `local_day`, `local_month`; domyślna waluta `APP_CURRENCY` (PLN).
- Strona startowa pokazuje logo i korzysta ze stylów marki; adresy assetów zawierają wersję z pliku `VERSION`, więc po wydaniu przeglądarki pobierają nowe pliki.
- Strefa czasowa daty wydania w `tools/version-release.php` ustawiana zmienną `RELEASE_TIMEZONE` (domyślnie `Europe/Warsaw`).

### Changed

- Repozytorium przeniesione do `https://github.com/marcinulezalka/simply-mainboard`: adresy `homepage` i `support` w `composer.json`.
- Nagłówek PHPDoc z prawami autorskimi i licencją w każdym pliku PHP paczki, wymuszany przez Pint (`header_comment` w `pint.json`): `composer format` dopisuje go, `composer lint` zgłasza brak.
- Zależności `illuminate/log`, `illuminate/mail`, `illuminate/support` i `illuminate/translation` zastąpione przez `laravel/framework` ^13.0 (CSRF, Vite i TrustProxies są tylko w pełnym frameworku).
- Standardy kodu: PSR-4 i PER Coding Style pilnowany przez Laravel Pint z presetem `per` (`pint.json`, `composer lint` / `composer format`); `laravel/pint` w `require-dev`.
- Mainboard jest paczką, a nie projektem: usunięto katalog `infrastructure/` (vhost Apache, pula PHP-FPM, certyfikaty SSL) i domenę lokalną `mainboard.local`; pakiet rozwijany jest w `packages/simplysmart/mainboard` projektu głównego i podpinany przez repozytorium Composera typu `path`.
- Wersję pakietu wydaje wspólny przybornik `simplysmart/tools` (https://github.com/marcinulezalka/tools) z projektu głównego: `vendor/bin/version-release --root=packages/simplysmart/mainboard`; własny katalog `tools/` pakietu usunięty.
- Instrukcja wydawania wersji przeniesiona do `CONTRIBUTING.md`; plik `USAGE.md` (jednorazowa aktywacja vhosta i nieaktualne kroki wdrożenia) usunięty.
- Katalog `scripts/` przemianowany na `tools/` (nazwy `bin`/`scripts` bywają zarezerwowane lub blokowane na hostingach współdzielonych); `.gitattributes` wyklucza `tools/` z paczek (`export-ignore`).
- README skrócone do opisu, wymagań, szybkiego startu i spisu dokumentacji; szczegóły przeniesione do `docs/`, które `.gitattributes` również wyklucza z paczek.
- `tools/version-release.php`: sekcja `### Deprecated` podbija MINOR zamiast PATCH (zgodnie z SemVer).
- Repozytorium przemianowane z `simplysmart` na `mainboard` (`https://github.com/marcinulezalka/mainboard`): adresy w `composer.json`, katalog projektu `mainboard/`, domena lokalna `mainboard.local`, paczka `git archive` jako `mainboard.zip`.

### Fixed

- `tools/version-release.php` nie tworzy pustego wydania, gdy `[Unreleased]` ma same puste nagłówki.
- `tools/version-release.php` odrzuca niepoprawny plik `VERSION` (np. `v1.2.3`, `1.2.3-beta.1`) czytelnym komunikatem — wcześniej `v1.2.3` po cichu stawało się `0.2.4`, a sufiks pre-release kończył się błędem krytycznym PHP.
- `tools/version-release.php` nie traktuje każdego wystąpienia słowa „breaking” (np. „line-breaking”) jako zmiany łamiącej.
- `tools/version-release.php` odrzuca nieznane sekcje (`### Dodano`) i wpisy bez nagłówka zamiast liczyć je jako PATCH, zgłasza brak sekcji `[Unreleased]` i nieznane opcje.
- `tools/version-release.php` zapisuje `VERSION` i `CHANGELOG.md` atomowo i zachowuje końce linii CRLF.
- Nazwa projektu „simplysmart” pisana małą literą w README i na stronie startowej.

## [0.1.0] - 2026-10-06

### Added

- `CONTRIBUTING.md` z zasadami wpisów w `CHANGELOG.md` i wydawania wersji.
- Initial scaffold wygenerowany przez Smartboard (2026-10-06).
