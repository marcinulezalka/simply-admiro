# Strefy

Strefa to odizolowana część aplikacji, która ma własny guard, model kont, ciasteczko i tabelę sesji, prefiks URL i prefiks nazw tras. Core (mainboard) dostarcza mechanizm, a paczka strefy go konfiguruje. Mainboard i skeleton nie mają własnej strefy.

Strefy: `admiro` — administratorzy (paczka `simplysmart/admiro`, migracja `create_admiro_tables`).

Status: etap 1 (rejestracja stref, sesja, język, nagłówki, zaufane proxy) i etap 2 (logowanie kodem z e-maila i PIN-em). Zaufane urządzenia i uprawnienia dojdą w kolejnych etapach.

## Jak dodać strefę

1. Paczka strefy ma config `config/<paczka>.php` z kluczem strefy:

   ```php
   'zone' => [
       'name' => 'shop',                 // nazwa: klucze cache, ciasteczko, grupa middleware
       'slug' => env('SHOP_SLUG', 'shop'), // segment URL; nie może być kodem języka
       'model' => ShopAccount::class,    // konta strefy; brak = strefa publiczna bez logowania
       // opcjonalnie: 'guard', 'provider', 'session' => ['lifetime' => 15, ...],
       // 'auth' => ['pin' => ['length' => 8]] (nadpisania mainboard.auth)
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
   | `zone.php` | `Authenticated` | `auth:<guard>` plus `permission:<x>,<guard>` w samej trasie |

   Strefa bez `model` może mieć tylko `public.php`.

4. Strefa z logowaniem:
   - model kont dziedziczy po `Simplysmart\Mainboard\Models\ZoneAccount` i ustawia tylko `$table = '<strefa>_accounts'`;
   - migracja w Core (`database/migrations/`) wywołuje `app(ZoneTables::class)->create('<strefa>')` w `up()` i `->drop('<strefa>')` w `down()`. Powstają tabele `<strefa>_accounts`, `<strefa>_login_challenges` i `<strefa>_sessions`;
   - strefa bez rejestracji zakłada konta akcją `Auth\CreateZoneAccount` (np. z komendy paczki, jak `admiro:account:create`): przy ustawionej `CurrentZone` tworzy aktywne konto bez PIN-u, a dla zajętego adresu rzuca `AccountAlreadyExists`. PIN właściciel ustawia przy pierwszym logowaniu.

## Co Core robi za strefę

- **Guard i provider**: `ZoneGuardRegistrar` dopisuje do `config/auth` guard `session` i provider `eloquent`. Plik `config/auth.php` aplikacji zostaje bez zmian.
- **Grupa middleware** `zone.<nazwa>` zastępuje grupę `web`. Kolejność: `EncryptCookies` → `AddQueuedCookiesToResponse` → `UseZone` → `StartSession` → `ShareErrorsFromSession` → CSRF bez ciasteczka `XSRF-TOKEN` → `SetLocale` → `SubstituteBindings` → `SecurityHeaders`.
- **Sesja** (`UseZone`, `ZoneSessionConfigurator`): ciasteczko `<nazwa>_session`, poza środowiskiem `local` zawsze `secure`.
  - Strefa z logowaniem: tabela `<nazwa>_sessions`, czas życia `ZONE_SESSION_LIFETIME` (30 min), wygaśnięcie po zamknięciu przeglądarki, `SameSite=Strict`, szyfrowanie.
  - Strefa publiczna: `SameSite=Lax`, pozostałe ustawienia z `config/session.php`.
  - Po odpowiedzi config sesji, domyślny guard i `CurrentZone` wracają do stanu aplikacji.
- **Strefa żądania**: kontrakt `Contracts\CurrentZone` (wiązanie `scoped`).
- **Język**: `SetLocale` i `Localization\LocaleRouting`, sterowane zmienną `APP_LOCALES`.
  - Jeden język: trasy bez prefiksu.
  - Kilka języków: prefiks `{locale}` zawsze obecny i ograniczony do listy. Parametr jest zdejmowany przed kontrolerem, a `route()` dostaje język bieżącego żądania.
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
- **Czyszczenie kodów:** `mainboard:auth:prune` co godzinę usuwa kody starsze niż 24 h.

**Rozszerzanie przez zdarzenia** (`Auth\Events`): `SignInSucceeded`, `SignInRejected`, `AccountLocked`, `SignedOut`. Core loguje je i wysyła mail o blokadzie; strefa dokłada własne listenery.

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
| `AUTH_LOG_CHANNEL` | `sign_in` | kanał dziennika logowań |
