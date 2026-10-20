# Strefy

Strefa to odizolowana część aplikacji, która ma własny guard, model kont, ciasteczko i tabelę sesji, prefiks URL i prefiks nazw tras. Core (mainboard) dostarcza mechanizm, a paczka strefy go konfiguruje. Mainboard i skeleton nie mają własnej strefy.

Status: etap 1, czyli rejestracja stref, sesja, język, nagłówki i zaufane proxy. Logowanie (kod e-mail + PIN), zaufane urządzenia i uprawnienia dojdą w kolejnych etapach.

## Jak dodać strefę

1. Paczka strefy ma config `config/<paczka>.php` z kluczem strefy:

   ```php
   'zone' => [
       'name' => 'shop',                 // nazwa: klucze cache, ciasteczko, grupa middleware
       'slug' => env('SHOP_SLUG', 'shop'), // segment URL; nie może być kodem języka
       'model' => ShopAccount::class,    // konta strefy; brak = strefa publiczna bez logowania
       // opcjonalnie: 'guard', 'provider', 'session' => ['lifetime' => 15, ...]
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

## Zmienne środowiska

| Zmienna | Domyślnie | Znaczenie |
| --- | --- | --- |
| `APP_LOCALES` | `pl` | włączone języki, rozdzielone przecinkami |
| `ZONE_SESSION_LIFETIME` | `30` | minuty sesji w strefie z logowaniem |
| `TRUSTED_PROXIES` | brak | `*` albo adresy/sieci proxy |
| `SECURITY_HSTS`, `SECURITY_HSTS_MAX_AGE` | `true`, `31536000` | HSTS na HTTPS |
| `SECURITY_CSP` | `true` | Content-Security-Policy z nonce |
