# Strefy

Strefa to odizolowana część aplikacji, która ma własny guard, model kont, ciasteczko i tabelę sesji, prefiks URL i prefiks nazw tras. Core (mainboard) dostarcza mechanizm, a paczka strefy go konfiguruje. Mainboard i skeleton nie mają własnej strefy.

Strefy publiczne (strefy z paczek prywatnych mają własne migracje i nie są tu wymieniane, zob. „Private Packages” w CLAUDE.md skeletonu):

| Strefa | Paczka | Przeznaczenie | Migracja |
| --- | --- | --- | --- |
| `admiro` | `simplysmart/admiro` | administratorzy | `create_admiro_tables` |

Status: rejestracja stref, sesja, język, nagłówki, zaufane proxy; logowanie kodem z e-maila i PIN-em z zaufanymi urządzeniami; zmiana PIN-u i akcje administracyjne na kontach; uprawnienia kont w organizacjach (role z plików JSON, bieżąca organizacja w żądaniu, trasy z uprawnieniem, nadawanie ról bez eskalacji, zakładanie właścicieli; `docs/baza-danych.md` §15). Maskowanie błędów i honeypot dojdą w kolejnym etapie.

## Jak dodać strefę

1. Paczka strefy ma config `config/<paczka>.php` z kluczem strefy:

   ```php
   'zone' => [
       'name' => 'shop',                 // nazwa: klucze cache, ciasteczko, grupa middleware
       'slug' => env('SHOP_SLUG', 'shop'), // segment URL; nie może być kodem języka
       'model' => ShopAccount::class,    // konta strefy; brak = strefa publiczna bez logowania
       // opcjonalnie: 'guard', 'provider', 'session' => ['lifetime' => 15, ...],
       // 'auth' => ['pin' => ['length' => 8]] (nadpisania mainboard.auth),
       // 'auth' => ['trusted_devices' => ['enabled' => false]] (kod z e-maila przy każdym logowaniu),
       // 'teams' => true (konta działają w organizacjach klientów; domyślnie organizacja operatora)
   ],
   ```

2. Provider paczki dziedziczy po `Simplysmart\Mainboard\Zones\ZoneServiceProvider`:
   - w `register()` łączy config przez `mergeConfigFrom`;
   - implementuje `routeNamePrefix()` (stały, np. `'shop'`, niezależny od configu), `zoneConfig()` (`config('shop.zone')`) i `routesPath()`.

3. Trasy w `routesPath()` dzieli się na pliki według poziomu dostępu (`Zones\AccessTier`). Ścieżki są względne, nazwy krótkie, a nazwa `dashboard` w strefie `shop` daje trasę `shop.dashboard`.

   | Plik | Poziom | Middleware dodane przez Core |
   | --- | --- | --- |
   | `public.php` | `Public` | brak: każdy odwiedzający i każde konto |
   | `account.php` | `Authenticated` | `auth:<guard>`: każde konto strefy |
   | `zone.php` | `Authenticated` | `auth:<guard>` plus `ZonePermission::middleware(<uprawnienie>)` w samej trasie |

   Strefa bez `model` może mieć tylko `public.php`.

4. Strefa z logowaniem:
   - model kont dziedziczy po `Simplysmart\Mainboard\Models\ZoneAccount` i ustawia tylko `$table = '<strefa>_accounts'`;
   - migracja w Core (`database/migrations/`) wywołuje `app(ZoneTables::class)->create('<strefa>')` w `up()` i `->drop('<strefa>')` w `down()`. Powstają tabele `<strefa>_accounts` (pełny standard tabel danych z historią `<strefa>_accounts_history` i pulą id od 1000), `<strefa>_login_challenges`, `<strefa>_sessions` i `<strefa>_trusted_devices`. Strefa utworzona wcześniej dostaje tabelę urządzeń migracją z `->createTrustedDevices('<strefa>')` (nic nie robi, gdy tabela już jest);
   - role i uprawnienia strefy są w pliku JSON (`docs/baza-danych.md` §15): strefa publiczna w `database/seeders/permissions/<strefa>.json` Core, paczka prywatna we własnym repozytorium, rejestrowanym w jej providerze przez `PermissionFiles::add()`;
   - strefa bez rejestracji zakłada konta komendą `<strefa>:account:create {email} {name}`: klasa w paczce dziedziczy po `Console\CreateZoneAccountCommand` i podaje tylko `$name`, `$description` i `zoneName()`. Walidację, strefę żądania i komunikaty daje baza, a konto tworzy akcja `Auth\CreateZoneAccount` (aktywne, bez PIN-u; zajęty adres → `AccountAlreadyExists`). PIN właściciel ustawia przy pierwszym logowaniu.

5. Widoki strefy budują się na layoucie Core `<x-mainboard::zone.layout :title="...">`. Core daje mu `$zone` i `$account` (konto zalogowane w guardzie strefy albo null): layout pokazuje „Zalogowano jako …” i „Wyloguj”, gdy konto jest. Proste strony to `Route::view(...)` bez kontrolera.

## Co Core robi za strefę

- **Guard i provider**: `ZoneGuardRegistrar` dopisuje do `config/auth` guard `session` i provider `eloquent`. Plik `config/auth.php` aplikacji zostaje bez zmian.
- **Grupa middleware** `zone.<nazwa>` zastępuje grupę `web`. Kolejność: `EncryptCookies` → `AddQueuedCookiesToResponse` → `UseZone` → `StartSession` → `ShareErrorsFromSession` → CSRF bez ciasteczka `XSRF-TOKEN` → `SetLocale` → `RecordHistoryActor` → `UseCurrentTeam` → `SubstituteBindings` → `SecurityHeaders`.
- **Sesja** (`UseZone`, `ZoneSessionConfigurator`): ciasteczko `<nazwa>_session`, poza środowiskiem `local` zawsze `secure`.
  - Strefa z logowaniem: tabela `<nazwa>_sessions`, czas życia `ZONE_SESSION_LIFETIME` (30 min), wygaśnięcie po zamknięciu przeglądarki, `SameSite=Strict`, szyfrowanie.
  - Strefa publiczna: `SameSite=Lax`, pozostałe ustawienia z `config/session.php`.
  - Po odpowiedzi config sesji, domyślny guard i `CurrentZone` wracają do stanu aplikacji.
- **Strefa żądania**: kontrakt `Contracts\CurrentZone` (wiązanie `scoped`).
- **Język**: `SetLocale` i `Localization\LocaleRouting`, sterowane zmienną `APP_LOCALES`.
  - Jeden język: trasy bez prefiksu.
  - Kilka języków: prefiks `{locale}` zawsze obecny i ograniczony do listy. Parametr jest zdejmowany przed kontrolerem, a `route()` dostaje język bieżącego żądania.
  - Słownik języków (`locale_languages.is_interface`, domyślny `is_default`) wybiera spośród `APP_LOCALES` języki włączone teraz (`Contracts\InterfaceLocales`, cache 5 min, `forget()` po zmianie). Język z adresu, który administrator wyłączył, przekierowuje GET na ten sam adres w języku domyślnym. `APP_LOCALES` pozostaje sufitem: trasy (i `route:cache`) nie zależą od bazy, więc nowy język wymaga wpisu w `.env` i tłumaczeń. Bez słownika albo bez języków interfejsu w jego granicach obowiązuje sam config.
- **Przekierowania** (`ZoneRedirects`):
  - niezalogowany trafia na `<strefa>.auth.login`, a gdy tej trasy nie ma, dostaje 404;
  - zalogowany na trasie tylko dla gości trafia na `<strefa>.dashboard`;
  - globalnej trasy `login` nie ma.
- **Nagłówki** (`SecurityHeaders`):
  - wszystkie strefy: `X-Frame-Options: DENY`, `nosniff`, `Referrer-Policy`, `Permissions-Policy`, `Cross-Origin-Opener-Policy`, HSTS na HTTPS oraz CSP z nonce przekazanym do Vite (pomijana, gdy działa serwer `vite` hot);
  - strefy z logowaniem dodatkowo: `Cache-Control: no-store` i `X-Robots-Tag`.
- **Zaufane proxy**: `TRUSTED_PROXIES` (`*` albo lista adresów) trafia do `TrustProxies`, więc IP klienta pochodzi z `X-Forwarded-For` tylko od zaufanego proxy.
- **Cache**: klucze buduje wyłącznie `Zones\ZoneCacheKey::make($strefa, ...)`, co daje postać `zone:<strefa>:...`.

Rejestr (`Contracts\ZoneRegistry`) odrzuca strefy o tej samej nazwie, guardzie, prefiksie nazw tras lub slugu.

## Logowanie: kod z e-maila i PIN

Core rejestruje w każdej strefie z logowaniem trasy `Http\SignInRoutes` (pod `signin/`, poziom `GuestOnly`, każdy POST z limitem `mainboard.sign-in`):

| Trasa | Znaczenie |
| --- | --- |
| `<strefa>.auth.login` | formularz adresu e-mail |
| `<strefa>.auth.code` | kod z e-maila; `auth.code.resend` wysyła nowy |
| `<strefa>.auth.pin-setup` | ustawienie PIN-u przy pierwszym logowaniu |
| `<strefa>.auth.pin` | PIN |
| `<strefa>.auth.logout` | wylogowanie (POST, poziom `Authenticated`) |

Trasy konta (`Http\AccountRoutes`, pod `account/`, poziom `Authenticated`, zmiany z limitem `mainboard.sign-in`):

| Trasa | Znaczenie |
| --- | --- |
| `<strefa>.account.pin` | formularz zmiany PIN-u; `account.pin.code` wysyła kod, `account.pin.update` zapisuje |
| `<strefa>.account.devices` | zaufane urządzenia konta; `account.devices.revoke` (jedno, po tokenie), `account.devices.revoke-all` |
| `<strefa>.account.organization.switch` | przełączenie bieżącej organizacji (strefa z `teams`) |

Trasy urządzeń istnieją tylko w strefie z zaufanymi urządzeniami. Layout strefy linkuje do zmiany PIN-u i urządzeń.

**Kroki.** Każdy krok implementuje `Contracts\SignInStep`. Lista kroków i ich kolejność pochodzą z `auth.steps`, a domyślnie są to `EmailCodeStep`, `PinSetupStep`, `PinStep`.
- `SignInFlow` wybiera pierwszy wymagany i niewykonany krok.
- `EnsureSignInStep` wpuszcza tylko na ten krok i tylko w czasie jego ważności, więc kroków nie da się przeskoczyć.
- Nowy krok (np. TOTP) to nowa klasa i wpis w configu, bez zmian w Core.

**Zabezpieczenia:**
- **Ta sama ścieżka dla każdego adresu.** Żądanie zawsze kolejkuje szyfrowany job `IssueSignInCode`, także dla nieznanego adresu. Dopiero job sprawdza konto i wysyła kod, więc odpowiedź ani jej czas nie zdradzają, czy konto istnieje. Na produkcji kolejka musi być asynchroniczna.
- **Jeden komunikat.** Każda porażka daje ten sam tekst (`SignInFailure`).
- **Kod:**
  - losowanie przez `random_int`, w bazie tylko HMAC-SHA256 z `APP_KEY`, porównanie przez `hash_equals`;
  - ważny tylko najnowszy kod, przez 10 minut, z 5 próbami;
  - nowy kod najwcześniej po 60 s, a podwójne wysłanie formularza nie wysyła drugiego.
- **PIN:**
  - domyślnie 6 cyfr, reguła `StrongPin` (bez powtórzeń i kolejnych cyfr);
  - przestarzały hash jest aktualizowany przy logowaniu.
- **Blokady:**
  - po 5 błędnych PIN-ach konto jest blokowane na 15 min, potem 60 min, potem 24 h;
  - licznik i poziom blokady zeruje dopiero udane logowanie, więc po blokadzie każdy błędny PIN blokuje dłużej;
  - o blokadzie dowiaduje się tylko właściciel (`AccountLockedMail`).
- **Limity** (`auth.limits`, klucze `zone:<strefa>:auth:...`):
  - per IP;
  - per para adres + IP;
  - per adres z dowolnego IP (rotacja adresów nie pomaga).
- **Sesja:**
  - regeneracja na starcie logowania, po kodzie i po zalogowaniu;
  - przy logowaniu do sesji trafia epoka konta (`auth_epoch`);
  - `EnsureAccountCanSignIn` (poziom `Authenticated`) wylogowuje sesję konta nieaktywnego, zablokowanego albo o innej epoce. `AccountStore::bumpAuthEpoch()` unieważnia więc wszystkie sesje konta.
  - Laravelowe remember-me jest wyłączone.
- **RODO:**
  - e-mail, IP i user agent są zaszyfrowane;
  - konto wyszukuje się po `email_hash` (HMAC, `Contracts\BlindIndex`);
  - dziennik `sign_in` (`AUTH_LOG_CHANNEL`) zapisuje identyfikator konta, nigdy adres.
- **Wygasły formularz (419)** w strefie z logowaniem wraca do logowania strefy.
- **Czyszczenie:** `mainboard:auth:prune` co godzinę usuwa kody starsze niż 24 h oraz odwołane i wygasłe zaufane urządzenia. Strefa bez tabeli urządzeń dostaje ostrzeżenie, a pozostałe są czyszczone dalej.

### Zaufane urządzenia

Na kroku kodu konto może zaznaczyć „Zapamiętaj to urządzenie” (`auth.trusted_devices`, domyślnie 30 dni, najwyżej 10 urządzeń na konto; strefa wymagająca kodu ustawia `enabled` na `false`, tak robi `admiro`).

- **Czynniki:** nowe urządzenie przechodzi kod z e-maila (posiadanie skrzynki) i PIN. Na zaufanym urządzeniu krok kodu jest pomijany, bo czynnikiem posiadania jest urządzenie (`EmailCodeStep::isRequired()`), ale PIN obowiązuje zawsze. Zapamiętać można tylko po kroku kodu, nigdy na ścieżce zaufanego urządzenia.
- **Token** (`Auth\Devices`): ciasteczko `<strefa>_device` = `selector.validator` (16 B i 32 B z `random_bytes`), szyfrowane, `HttpOnly`, `SameSite=Strict`, `secure` jak sesja strefy, ważne do końca zaufania (bez przedłużania). W bazie jest tylko HMAC-SHA256 validatora z `APP_KEY`.
- **Rotacja i kradzież:** każde użycie zmienia validator. Stary validator przy zgodnym selectorze oznacza skopiowane ciasteczko: Core unieważnia dostęp do konta (sesje, wszystkie urządzenia) i wysyła właścicielowi `DeviceTokenReusedMail`.
- **Granice:** ciasteczko innego konta lub strefy jest ignorowane (zwykły kod, nic nie wycieka). Inna przeglądarka lub system niż zapamiętane (`DeviceNameResolver`, bez fingerprintu) kończy zaufanie urządzenia.
- **Odwołania:** każde unieważnienie dostępu do konta i każda blokada za PIN-y odwołuje wszystkie jego urządzenia. Właściciel odwołuje je też sam z panelu; odwołanie bieżącego usuwa ciasteczko.
- **Powiadomienie:** nowe urządzenie → `NewTrustedDeviceMail` z linkiem do panelu.

### Zmiana PIN-u i akcje administracyjne

- **Zmiana PIN-u** (`Auth\ChangePin`) wymaga obecnego PIN-u (liczonego do blokady jak przy logowaniu) i kodu z e-maila, a nowy PIN musi być inny. Limit `auth.limits.pin_changes` liczy próby per konto, niezależnie od IP. Potem pozostałe sesje i urządzenia są unieważniane, bieżąca sesja zostaje, a właściciel dostaje `PinChangedMail`.
- **Akcje administracyjne** (`Auth\AccountAdministration`, bez UI): `resetPin()` (właściciel ustawi PIN po kodzie przy logowaniu), `unlock()`, `deactivate()`. Działają w strefie konta (`Zones\AccountZone`), także gdy administrator jest w innej strefie, i zapisują aktora (`Auth\AccountActor`).
- **Unieważnienie dostępu** (`Auth\RevokeAccountAccess`) to jedno miejsce dla wszystkich powyższych: nowa epoka konta, usunięcie zapisanych sesji z `<strefa>_sessions` i zdarzenie `AccountAccessRevoked` z powodem (`AccessRevocationReason`).

**Rozszerzanie przez zdarzenia** (`Auth\Events`): `SignInSucceeded`, `SignInRejected`, `AccountLocked`, `SignedOut`, `AccountAccessRevoked`, `PinChanged`, `TrustedDeviceRegistered`, `TrustedDevicesRevoked`, a z uprawnień `Permissions\Events\ZoneRoleChanged`. Core zapisuje je w dzienniku `sign_in`, wysyła maile i odwołuje urządzenia; strefa dokłada własne listenery.

## Zmienne środowiska

| Zmienna | Domyślnie | Znaczenie |
| --- | --- | --- |
| `APP_LOCALES` | `pl` | włączone języki, rozdzielone przecinkami |
| `ZONE_SESSION_LIFETIME` | `30` | minuty sesji w strefie z logowaniem |
| `TRUSTED_PROXIES` | brak | `*` albo adresy/sieci proxy |
| `SECURITY_HSTS`, `SECURITY_HSTS_MAX_AGE` | `true`, `31536000` | HSTS na HTTPS |
| `SECURITY_CSP` | `true` | Content-Security-Policy z nonce |
| `AUTH_CODE_TTL` | `10` | minuty ważności kodu z e-maila |
| `AUTH_PIN_LENGTH` | `6` | długość PIN-u (minimum 4) |
| `AUTH_TRUSTED_DEVICES` | `true` | zaufane urządzenia (strefa może je wyłączyć w configu) |
| `AUTH_TRUSTED_DEVICE_DAYS` | `30` | dni zaufania urządzenia |
| `AUTH_LOG_CHANNEL` | `sign_in` | kanał dziennika logowań |

## Uprawnienia

- Model kont strefy (`ZoneAccount`) ma role i uprawnienia spatie w guardzie strefy, odmowy
  (`Permissions\Concerns\HasZonePermissions`, kontrakt `Contracts\HasZoneRoles`) i członkostwa
  w organizacjach (`organizations()`). Guard spatie wykrywa z providera strefy.
- **Organizacja żądania** (`Http\Middleware\UseCurrentTeam`, kontrakt `Contracts\CurrentOrganization`):
  strefa bez `teams` działa w organizacji operatora; strefa z `teams` w organizacji wybranej przez konto
  (sesja strefy, `account.organization.switch`), o ile nadal jest jego aktywnym członkostwem, inaczej
  w pierwszej. Bez członkostwa nie ma organizacji, więc nie ma uprawnień. Po odpowiedzi organizacja jest czyszczona.
- **Trasa z uprawnieniem:** `ZonePermission::middleware(Permission::ReportsView)` daje middleware
  `zone.permission:<uprawnienie>`, który sprawdza konto z guardu strefy przez `ZonePermissionChecker`
  (z odmowami); brak uprawnienia to 403. W kodzie: `ZonePermissionChecker::allows($zone, $account, $permission)`.
- **Rekordy organizacji:** model dziedziczący po `Models\OrganizationDataModel` wiąże się w trasie tylko
  w bieżącej organizacji (rekord innej organizacji to 404, zanim zadziała uprawnienie czy Policy).
- **Role i ich nadawanie:** pliki JSON, synchronizacja, nadawanie bez eskalacji i zakładanie właścicieli
  opisuje `docs/baza-danych.md` §15.
