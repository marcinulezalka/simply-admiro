# Strefy

Strefa to odizolowana część aplikacji, która ma własny guard, model kont, ciasteczko i tabelę sesji, prefiks URL i prefiks nazw tras. Rdzeń paczki admiro dostarcza mechanizm, a paczka strefy go konfiguruje (strefa administratorów `admiro` z `src/Panel/` też). Rdzeń i skeleton nie mają własnej strefy.

Strefy publiczne (strefy z paczek prywatnych mają własne migracje i nie są tu wymieniane, zob. „Private Packages” w CLAUDE.md skeletonu):

| Strefa | Paczka | Przeznaczenie | Migracja |
| --- | --- | --- | --- |
| `admiro` | `simplysmart/admiro` | administratorzy | `create_admiro_tables` |

Status: rejestracja stref, sesja, język, nagłówki, zaufane proxy; logowanie kodem z e-maila i PIN-em z zaufanymi urządzeniami; zmiana PIN-u i akcje administracyjne na kontach; uprawnienia kont w organizacjach (role z plików JSON, bieżąca organizacja w żądaniu, role jako konteksty, trasy z uprawnieniem, nadawanie ról bez eskalacji, zakładanie właścicieli; `docs/baza-danych.md` §15). Layout strefy z menu ustawień. Do tego maskowanie błędów, blokada agentów AI i pułapki na boty.

## Jak dodać strefę

1. Paczka strefy ma config `config/<paczka>.php` z kluczem strefy:

   ```php
   'zone' => [
       'name' => 'shop',                 // nazwa: klucze cache, ciasteczko, grupa middleware
       'slug' => env('SHOP_SLUG', 'shop'), // segment URL; nie może być kodem języka
       'model' => ShopAccount::class,    // konta strefy; brak = strefa publiczna bez logowania
       // opcjonalnie: 'guard', 'provider', 'session' => ['lifetime' => 15, ...],
       // 'auth' => ['pin' => ['min_length' => 8]] (nadpisania admiro.auth; zakres PIN-u w granicach 4–16),
       // 'auth' => ['trusted_devices' => ['enabled' => false]] (kod z e-maila przy każdym logowaniu),
       // 'auth' => ['layout' => 'split'] (ekrany logowania jako dwie karty: marka i formularz; domyślnie 'centered';
       //     'split-reversed' — to samo w odbiciu: formularz po lewej, marka po prawej),
       // 'auth' => ['logo' => __DIR__.'/../public/images/logo.svg'] (logo strefy w karcie marki zamiast logo simplysmart;
       //     SVG z fill="currentColor" osadzany inline w kolorze karty; brak pliku zatrzymuje start),
       // 'auth' => ['home_link' => false] (bez przycisku „Strona główna” pod formularzem logowania; domyślnie jest),
       // 'auth' => ['legal' => ['cookie_policy_first' => true, 'cookie_settings' => false]] (polityka cookies przed
       //     dokumentami strefy, bez „Ustawień cookies” w karcie logowania; nazwy odnośników z tekstów
       //     `sign_in.<strefa>.legal_cookies|legal_privacy|legal_gdpr`),
       // 'social_links' => ['linkedin' => 'https://…', 'github' => 'https://…'] (Enums\SocialNetwork → adres http(s);
       //     puste pomijane, nieznany serwis albo zły adres zatrzymuje start; w menu konta w stopce layoutu Workspace),
       // 'app_layout' => 'workspace' (Enums\AppLayout: bez menu bocznego, cała nawigacja w przyklejonej stopce — konto
       //     z menu, skróty, wyszukiwarka, przełącznik kontekstu, powiadomienia, kalendarz i mapa strony; z Contracts\ZoneSitemap
       //     mapa pokazuje sitemap(), z Contracts\ZoneShortcuts obok konta są skróty shortcuts(); domyślnie `sidebar`;
       //     menu strefy z Contracts\ZoneAppLayout wybiera wariant dla bieżącego żądania, np. Workspace w kontekście jednej roli),
       // 'font_styles' => ['serif' => '<paczka>::appearance.fonts.serif'] (kroje do wyboru na stronie „Personalizacja” obok kroju
       //     bazowego strefy: klucz kroju — małe litery, cyfry i myślniki — → klucz tekstu z nazwą; arkusz strefy przestawia
       //     tokeny --ss-font-* dla :root[data-font="serif"] i daje rodzinę próbce [data-ss-font-sample="serif"]; fonty
       //     z `fonts`; zły klucz albo nazwa zatrzymuje start),
       // 'appearance' => ['account' => true, 'defaults' => ['layout-width' => 'centered']] (tylko strefa z logowaniem:
       //     wybory wyglądu zapisuje konto — `json_attributes.appearance`, Profile\AccountAppearance — zamiast przeglądarki,
       //     więc obowiązują na każdym urządzeniu; serwer rysuje je na <html> bez błysku, a js/theme.js wysyła każdą zmianę
       //     na `account.appearance.update`; `defaults` to domyślny wygląd aplikacji, który konto nadpisuje — ustawienia
       //     Enums\AppearanceSetting: theme, palette, text-size, layout-width, font; puste wartości pomijane, wartość spoza
       //     wyboru strefy zatrzymuje start; domyślnie wybory zapamiętuje przeglądarka),
       // 'backdrop' => 'zone-logo' (Enums\AppBackdrop: tło płótna po zalogowaniu — logo strefy `logo` wtopione w tło przy
       //     dolnej krawędzi okna (przycięte stopką na dole) zamiast logotypu simplysmart na środku; wymaga logo strefy;
       //     domyślnie `brand`),
       // 'auth' => ['brand' => ['light' => __DIR__.'/../public/images/banner.jpg', 'dark' => ..., 'alt' => '<paczka>::...']]
       //     (baner w karcie marki zamiast logo; Core serwuje pliki trasą <strefa>.auth.brand z publicznym cache),
       // 'stylesheets' => [__DIR__.'/../public/css/<strefa>.css'] (style strefy po stylach Core: logowanie, layout
       //     aplikacji i strona publiczna; tylko tokeny --ss-*; brak pliku zatrzymuje start),
       // 'palettes' => ['teal', 'blue'] (kolorystyki do wyboru, wartości Enums\Palette, nakładane na kolory bazowe
       //     strefy; przełącznik obok motywu; nieznana lub powtórzona wartość zatrzymuje start),
       // 'favicon' => ['ico' => __DIR__.'/../public/images/favicon.ico', 'svg' => ..., 'apple' => ...png]
       //     (ikony strony strefy, każda opcjonalna; Core serwuje je trasą <strefa>.favicon z wersją w adresie
       //     i publicznym cache, a x-admiro::page w strefie dodaje znaczniki <link>; bez klucza ikona aplikacji),
       // 'fonts' => ['brand-latin' => ['family' => 'Brand Sans', 'weight' => 400, 'style' => 'normal',
       //     'file' => __DIR__.'/../public/fonts/brand-latin.woff2', 'unicode_range' => 'U+0000-00FF']]
       //     (pliki .woff2 strefy; Core serwuje je trasą <strefa>.font z wersją w adresie i publicznym cache, zgodnie
       //     z CSP font-src 'self', a x-admiro::page w strefie dodaje reguły @font-face; arkusz strefy przypisuje
       //     rodziny tokenom --ss-font-sans, --ss-font-base, --ss-font-display, --ss-font-accent; zły wpis zatrzymuje start),
       // 'legal_links' => [['route' => 'legal.privacy', 'label' => '<paczka>::legal.privacy.link'], ...]
       //     (krótka nazwa trasy strefy i klucz tekstu; logowanie pokazuje je na dole karty formularza razem
       //     przed polityką i ustawieniami cookies, .ss-auth__legal — bez stopki strony simplysmart; layout aplikacji
       //     w stopce simplysmart; tylko strefa z logowaniem),
       // 'legal_documents' => ['privacy', 'gdpr'] (dokumenty prawne Core — Enums\LegalDocumentType — linkowane w karcie
       //     logowania, stopce layoutu aplikacji i e-mailach strefy przed `legal_links`, tylko opublikowane; strony
       //     dokumentów to trasy Core admiro.legal.*; nieznana lub powtórzona wartość zatrzymuje start),
       // 'brand' => ['logo' => __DIR__.'/../public/images/logo.png', 'signet' => ...png]
       //     (marka strefy, PNG/JPG — klienci poczty nie pokazują SVG; logo trafia do e-maili, gdy organizacja nie ma
       //     własnego; brak pliku, zły format albo `favicon` w tej sekcji zatrzymuje start),
       // 'logo' => __DIR__.'/../public/images/logo.svg' (logo strefy do osadzenia inline na stronach, np. pod marką
       //     na stronie startowej — View\ZoneLogos; SVG z fill="currentColor", domyślnie `auth.logo`; zły plik zatrzymuje start),
       // 'website' => 'https://shop.example' (strona WWW strefy: logo strefy na stronie startowej prowadzi do niej
       //     w nowej karcie, przy wspólnym logo kilku stref pierwszy podany adres; adres spoza http(s) zatrzymuje start),
       // 'mail' => ['views' => '<paczka>::mail.<strefa>', 'palette' => ['accent' => '#112a46', ...]]
       //     (e-maile strefy, patrz „E-maile strefy”; zły prefiks albo kolor spoza #rrggbb zatrzymuje start),
       // 'teams' => true (konta działają w organizacjach klientów; domyślnie organizacja operatora),
       // 'role_contexts' => true (tylko z `teams`: role konta w organizacji są kontekstami — patrz „Uprawnienia”),
       // 'security' => ['mask_errors' => true, 'block_ai_agents' => false] (domyślnie: maskowanie
       // jak admiro.security.mask_errors, agenci AI blokowani w strefach z logowaniem)
   ],
   ```

2. Provider paczki dziedziczy po `Simplysmart\Admiro\Zones\ZoneServiceProvider`:
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
   - model kont dziedziczy po `Simplysmart\Admiro\Models\ZoneAccount` i ustawia tylko `$table = '<strefa>_accounts'`;
   - migracja w Core (`database/migrations/`) wywołuje `app(ZoneTables::class)->create('<strefa>')` w `up()` i `->drop('<strefa>')` w `down()`. Powstają tabele `<strefa>_accounts` (pełny standard tabel danych z historią `<strefa>_accounts_history` i pulą id od 1000), `<strefa>_login_challenges`, `<strefa>_sessions` i `<strefa>_trusted_devices`. Strefa utworzona wcześniej dostaje tabelę urządzeń migracją z `->createTrustedDevices('<strefa>')` (nic nie robi, gdy tabela już jest);
   - role i uprawnienia strefy są w pliku JSON (`docs/baza-danych.md` §15): strefa publiczna w `database/seeders/permissions/<strefa>.json` Core, paczka prywatna we własnym repozytorium, rejestrowanym w jej providerze przez `PermissionFiles::add()`;
   - strefa bez rejestracji zakłada konta komendą `<strefa>:account:create {email} {name} [--role=…] [--organization=<token>]`: klasa w paczce dziedziczy po `Console\CreateZoneAccountCommand` i podaje tylko `$name`, `$description` i `zoneName()`. Walidację, strefę żądania i komunikaty daje baza, a konto tworzy akcja `Auth\CreateZoneAccount` (aktywne, bez PIN-u; zajęty adres → `AccountAlreadyExists`). PIN właściciel ustawia przy pierwszym logowaniu. `--role` nadaje role przez `Permissions\SystemRoleGrant` (bez sprawdzania eskalacji, tylko konsola i zakładanie właścicieli) w organizacji z `--organization`, a w strefie bez `teams` w organizacji operatora; nieznana rola cofa założenie konta.

5. Widoki strefy budują się na layoucie Core `<x-admiro::zone.layout :title="...">` (opcjonalnie `meta` — opis pod tytułem krojem mono —, `back`, `icon` — bez niej ikona bieżącej pozycji menu albo mapy strony — i slot `actions`, panel akcji w nagłówku). Core daje mu `$zone`, `$account` (konto zalogowane w guardzie strefy albo null) i organizacje konta. Dla zalogowanego konta layout to layout aplikacji z menu bocznym (`x-admiro::app.layout`): awatar w kolorze konta z drugą linią, menu konta (profil, bezpieczeństwo, wygląd), ikony ustawień i powiadomień pod nazwą konta, wylogowanie na dole menu, przełącznik kontekstu (organizacja, gdy konto ma kilka; rola w strefie z `role_contexts`, gdy konto ma kilka ról), komunikaty `status` jako toasty, stopka simplysmart z odnośnikami prawnymi strefy (`legal_links`). Menu, ustawienia strefy, globalną wyszukiwarkę i drugą linię podaje strefa: klasa `Contracts\ZoneNavigation` (pozycje według uprawnień konta, bieżąca przez `current`) zarejestrowana w providerze strefy przez `Zones\ZoneNavigations::add('<strefa>', Klasa::class)`; strefa bez niej ma layout bez pozycji menu i same ustawienia konta. **Ustawienia** (`Zones\ZoneSettingsMenu`, `View\Navigation\SettingsMenu`): każda strefa ma osobne menu ustawień — `ZoneNavigation::settings()` daje grupy konfiguracji strefy posegregowane tematycznie, Core dokłada po nich grupę „Konto” (profil, bezpieczeństwo, wygląd: motyw i kolorystyka). Ikona ustawień (suwaki) pod nazwą konta prowadzi do pierwszej pozycji; na stronie, której pozycja ustawień jest bieżąca, menu boczne przełącza się na menu ustawień z „Wróć do aplikacji” (pulpit strefy). Pozycja nie może być jednocześnie w `groups()` i `settings()`. Gość (strona publiczna strefy) dostaje prostą kolumnę treści. Proste strony to `Route::view(...)` bez kontrolera.

## Co Core robi za strefę

- **Guard i provider**: `ZoneGuardRegistrar` dopisuje do `config/auth` guard `session` i provider `eloquent`. Plik `config/auth.php` aplikacji zostaje bez zmian.
- **Grupa middleware** `zone.<nazwa>` zastępuje grupę `web`. Kolejność: `EncryptCookies` → `AddQueuedCookiesToResponse` → `UseZone` → `BlockAiAgents` → `StartSession` → `ShareErrorsFromSession` → CSRF bez ciasteczka `XSRF-TOKEN` → `SetLocale` → `RecordHistoryActor` → `UseCurrentTeam` → `SubstituteBindings` → `SecurityHeaders`.
- **Sesja** (`UseZone`, `ZoneSessionConfigurator`): ciasteczko `<nazwa>_session`, poza środowiskiem `local` zawsze `secure`.
  - Strefa z logowaniem: tabela `<nazwa>_sessions`, czas życia `ZONE_SESSION_LIFETIME` (30 min), wygaśnięcie po zamknięciu przeglądarki, `SameSite=Strict`, szyfrowanie.
  - Strefa publiczna: `SameSite=Lax`, pozostałe ustawienia z `config/session.php`.
  - Po odpowiedzi config sesji, domyślny guard i `CurrentZone` wracają do stanu aplikacji.
- **Strefa i konto żądania**: kontrakt `Contracts\CurrentZone` (wiązanie `scoped`) i `Contracts\CurrentAccount` (konto z guardu strefy żądania; `require()` daje 403 bez konta). Kod Core i stref nie szuka konta w guardzie sam; identyfikator konta daje `Auth\AccountKey`.
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

Core rejestruje w każdej strefie z logowaniem trasy `Http\SignInRoutes` (pod `signin/`, poziom `GuestOnly`, każdy POST z limitem `admiro.sign-in`):

| Trasa | Znaczenie |
| --- | --- |
| `<strefa>.auth.login` | formularz adresu e-mail |
| `<strefa>.auth.code` | kod z e-maila; `auth.code.resend` wysyła nowy |
| `<strefa>.auth.pin-setup` | ustawienie PIN-u przy pierwszym logowaniu; potem zawsze `<strefa>.dashboard`, bez powrotu na stronę zamierzoną sprzed logowania (`CompleteSignIn`) |
| `<strefa>.auth.pin` | PIN |
| `<strefa>.auth.logout` | wylogowanie (POST, poziom `Authenticated`) |

Trasy konta (`Http\AccountRoutes`, pod `account/`, poziom `Authenticated`, zmiany z limitem `admiro.sign-in`):

| Trasa | Znaczenie |
| --- | --- |
| `<strefa>.account.profile` | profil: imię i nazwisko, adres e-mail (tylko odczyt), język interfejsu (przy kilku włączonych; `json_attributes.locale`, `Profile\AccountLocale`, po logowaniu dashboard w tym języku), kolor awatara; w strefie z `teams` organizacje konta z rolami (`roleLabel()`: `<texts>::roles.<strefa>.<rola>`) i przełącznikiem; `account.profile.update` zapisuje (`Profile\UpdateProfile`) |
| `<strefa>.account.security` | bezpieczeństwo: zmiana PIN-u (`#pin`; `account.pin.code` wysyła kod, `account.pin.update` zapisuje), zaufane urządzenia (`#devices`; `account.devices.revoke` po tokenie, `account.devices.revoke-all`), ostatnie zdarzenia konta (`Queries\Audit\AccountHistory`, bez IP), `account.security.sign-out-everywhere` (DELETE: `RevokeAccountAccess` z powodem `SignedOutEverywhere` i wylogowanie) |
| `<strefa>.account.pin`, `<strefa>.account.devices` | dawne adresy sekcji: przekierowanie do `account.security#pin` / `#devices` |
| `<strefa>.account.appearance` | „Personalizacja”: szerokość treści, motyw (`x-admiro::theme-toggle`), kolorystyka (gdy strefa ma `palettes`), krój pisma (gdy ma `font_styles`) i rozmiar tekstu; wybory zapamiętuje przeglądarka, a w strefie z `appearance.account` konto: `account.appearance.update` (PUT, JSON → 204, każda zmiana przełącznika osobno) i `account.appearance.reset` (DELETE, wraca domyślny wygląd aplikacji) |
| `<strefa>.account.organization.switch` | przełączenie bieżącej organizacji (strefa z `teams`) |
| `<strefa>.account.role.switch` | przełączenie aktywnej roli (strefa z `role_contexts`; POST `role`, rola spoza ról konta to 404) i przekierowanie na pulpit strefy |

Trasy urządzeń istnieją tylko w strefie z zaufanymi urządzeniami. Menu konta w layoucie strefy: profil, bezpieczeństwo, wygląd (te same pozycje co grupa „Konto” w ustawieniach); wylogowanie jest na dole menu bocznego. Pola PIN-u mają klawiaturę ekranową (`x-admiro::pin-field`, `docs/design-system.md`). Etykiety ról w profilu biorą teksty paczki strefy z `texts` w configu strefy (namespace, np. `'texts' => 'shop'`).

Ekrany logowania (`auth/*`) czytają teksty przez `ZoneDefinition::signInText()`: najpierw
`<texts>::sign_in.<strefa>.<klucz>` (osobno dla strefy, gdy kilka stref dzieli paczkę tekstów), potem
`<texts>::sign_in.<klucz>`, na końcu Core `sign_in.<klucz>`. Poza tekstami Core strefa może dać wstęp i notkę
każdego kroku (`lead`, `note`, `code_note`, `pin_lead`, `pin_note`, `pin_setup_note`; w Core puste), przykład adresu
(`email_placeholder`) i przyciski (`submit`, `pin_setup_submit`); nagłówek może mieć wyróżniony fragment
w `*gwiazdkach*` (`View\AccentText`). Tytuł karty przeglądarki (`<title>`) to nagłówek ekranu bez gwiazdek, chyba że
strefa da `page_title` (w Core pusty) — wtedy jest nim na każdym ekranie logowania (np. sama nazwa strefy).

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
- **Czyszczenie:** `admiro:auth:prune` co godzinę usuwa kody starsze niż 24 h oraz odwołane i wygasłe zaufane urządzenia. Strefa bez tabeli urządzeń dostaje ostrzeżenie, a pozostałe są czyszczone dalej.

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
- **Zapasowy adres e-mail** (`Auth\Recovery\RecoveryEmailRegistration`, sekcja `#recovery` strony bezpieczeństwa): adres inny niż adres konta, potwierdzony kodem wysłanym na ten adres; zapisany szyfrowany w `recovery_email` (`recovery_email_set_at`), dodanie i usunięcie w dzienniku (`RecoveryEmailChanged`, bez adresu). Strefa utworzona wcześniej dostaje kolumny migracją z `ZoneTables::addRecoveryEmail()`.
- **Reset PIN-u przez zapasowy adres** (`Auth\Recovery\RecoveryPinReset`, strona „Nie pamiętam PIN-u”): gość podaje adres konta, kod idzie na potwierdzony zapasowy adres, poprawny kod usuwa PIN, zdejmuje blokadę i unieważnia dostęp (`AccessRevocationReason::PinResetWithRecoveryEmail`); nowy PIN ustawia się przy logowaniu kodem z adresu konta, więc do przejęcia konta trzeba obu skrzynek. Odpowiedź jest taka sama dla każdego adresu. Kody poza logowaniem (`Auth\Recovery\SessionCodes`, `Mail\SecurityCodeMail`) są w sesji strefy, osobno dla każdego celu, nigdy w tabeli kodów logowania — kodem resetu nie da się zalogować.
- **Akcje administracyjne** (`Auth\AccountAdministration`, bez UI): `resetPin()` (właściciel ustawi PIN po kodzie przy logowaniu), `unlock()`, `deactivate()`. Działają w strefie konta (`Zones\AccountZone`), także gdy administrator jest w innej strefie, i zapisują aktora (`Auth\AccountActor`).
- **Unieważnienie dostępu** (`Auth\RevokeAccountAccess`) to jedno miejsce dla wszystkich powyższych: nowa epoka konta, usunięcie zapisanych sesji z `<strefa>_sessions` i zdarzenie `AccountAccessRevoked` z powodem (`AccessRevocationReason`).

**Rozszerzanie przez zdarzenia** (`Auth\Events`): `SignInSucceeded`, `SignInRejected`, `AccountLocked`, `SignedOut`, `AccountAccessRevoked`, `PinChanged`, `RecoveryEmailChanged`, `TrustedDeviceRegistered`, `TrustedDevicesRevoked`, a z uprawnień `Permissions\Events\ZoneRoleChanged`. Core zapisuje je w dzienniku zdarzeń, wysyła maile i odwołuje urządzenia; strefa dokłada własne listenery.

**Dziennik zdarzeń** (`Audit\AuditEntries` to jedyne mapowanie zdarzenia na wpis): każde zdarzenie trafia do pliku
(`AUTH_LOG_CHANNEL`) i do tabeli `audit_events` (podgląd w panelu administratora). Wpis ma strefę, konto, aktora
(akcje administratorów: `Audit\Events\AdminActionPerformed`), organizację, szyfrowane IP i kontekst bez adresów
e-mail. Otwarcie rekordu z danymi osobowymi (np. kartoteki) zgłasza strefa zdarzeniem `Audit\Events\RecordAccessed`
(typ i token rekordu, flaga danych wrażliwych, opcjonalnie organizacja oglądającego `viewerOrganizationId`, gdy
rekord należy do innej organizacji), więc trafia do dziennika organizacji, do której rekord należy. Dziennik jednej organizacji
(jej zdarzenia i zdarzenia kont jej członków) daje `Queries\Audit\AuditLog::forOrganization()`. `admiro:audit:prune` codziennie usuwa wpisy starsze niż `AUDIT_RETENTION_DAYS` (365 dni).

## E-maile strefy

E-maile Core (kod logowania, blokada konta, zmiana PIN-u, nowe zaufane urządzenie, skopiowany token urządzenia)
dziedziczą po `Mail\ZoneMailable` i idą jako HTML z wersją tekstową. Mail niesie nazwę strefy, organizację i język,
a wysyłka i podgląd działają wewnątrz strefy (`CurrentZone::within`), więc w workerze kolejki wygląda tak samo jak
w żądaniu. Nowy e-mail strefy dziedziczy po `ZoneMailable` (konstruktor: strefa, id organizacji, dane), podaje
`template()` i `subjectKey()` oraz widoki `admiro::mail.html.<szablon>` (rozszerza `$mailLayout`) i
`admiro::mail.<szablon>` (tekst) — albo tylko widoki w paczce strefy.

| Element | Strefa z `mail.views` = `<prefiks>` | Bez `mail.views` |
| --- | --- | --- |
| layout HTML | `<prefiks>.layout` (wymagany; brak zatrzymuje wysyłkę) | `admiro::mail.layout` |
| treść HTML | `<prefiks>.<szablon>`, gdy istnieje | `admiro::mail.html.<szablon>` |
| wersja tekstowa | `<prefiks>.text.<szablon>`, gdy istnieje | `admiro::mail.<szablon>` |
| teksty | `<namespace texts>::emails.*`, potem Core (`ZoneDefinition::textKey`) | Core `emails.*` |
| kolory | `mail.palette` (`bg`, `surface`, `border`, `text`, `muted`, `accent`, `on_accent`; brakujące z Core) | paleta Core |

Layout strefy może dołączyć ramę Core (`@include('admiro::mail.frame')`: tabele 600 px, logo, karta treści,
stopka z odnośnikami prawnymi strefy `legal_links` i copyrightem) albo mieć własny HTML. Style są inline, bo klienci
poczty nie czytają arkuszy ani tokenów `--ss-*`. Widoki dostają `$mailZone`, `$mailPalette`, `$mailLogo`,
`$mailOrganization`, `$mailLegalLinks` i `$mailBrandGroup`.

Grupa logotypów (`$mailBrandGroup`, list<`Mail\MailBrand`>: nazwa i logo PNG) pochodzi z rejestru `Mail\MailBrandGroups`
(singleton): paczka dodaje w providerze markę do grupy strefy — `add('<strefa>', '<nazwa>', '<ścieżka PNG>', <pozycja>)`,
mniejsza pozycja wcześniej — także do grupy innej strefy (np. produkt, który ta strefa obsługuje). Strefa bez wpisów
ma pustą grupę i layout jej nie pokazuje.

Przykład własnego HTML: strefa administratorów (`admiro::mail.admiro`) ma layout w motywie sygnetu: karta z cieniem,
u góry pasek w grafitowym gradiencie z koroną wyciętą z lewego dolnego rogu i białym logotypem admiro po prawej, wyśrodkowanym w pionie
(jeden obraz `public/images/mail-header.png`, źródło `brand/mail/mail-header.svg`; layout nie pokazuje `$mailLogo`), pod nim treść, a na dole karty grupa logotypów z etykietą „Rodzina produktów
simplysmart” (simplysmart na pozycji 10, admiro na 30; pozycja 20 zostaje dla produktu). Szablony kodów (`login-code`, `security-code`) pokazują każdą cyfrę
w osobnym polu (`admiro::mail.admiro.partials.code`), jak pola kodu na stronie logowania.

Logo (`Mail\MailBrandResolver`) jest osadzone w wiadomości (CID): logo organizacji z panelu (`Organizations\OrganizationBranding`,
pliki na prywatnym dysku `ADMIRO_ORGANIZATION_BRAND_DISK`, domyślnie `local`), potem `brand.logo` strefy, a na końcu
logo simplysmart (`public/assets/img/brand/mail-logo.png`). Organizację maila konta wybiera `Mail\MailOrganization`:
strefa bez `teams` — organizacja operatora, strefa z `teams` — jedyna aktywna organizacja konta, przy kilku albo
żadnej logo strefy.

## Boty i maskowanie błędów

- **Maskowanie błędów** (`Security\ErrorMasker`): odpowiedź o statusie z `admiro.security.masked_statuses`
  (401, 403, 405, 419, 429) dostaje zwykłą stronę 404, więc odwiedzający nie odróżni braku dostępu od braku
  strony. Domyślnie tylko na produkcji (`SECURITY_MASK_ERRORS`), strefa nadpisuje to `security.mask_errors`.
  Wygasły formularz w strefie z logowaniem dalej wraca do logowania, a 503 nie jest maskowany. Core ustawia
  `respondUsing()` handlera wyjątków; aplikacja, która ustawi własny callback, wyłącza maskowanie Core.
- **Agenci AI** (`Http\Middleware\BlockAiAgents`, lista `admiro.security.ai_agents`): w strefie
  z `security.block_ai_agents` (domyślnie strefy z logowaniem) dostają 403, zanim powstanie sesja.
- **Pułapki na boty** (`admiro.honeypot`, domyślnie wyłączone):
  - `HONEYPOT_ROUTES`: pod adresami sprawdzanymi przez boty (`honeypot.paths`: `wp-login.php`, `admin`,
    `.env`…) Core pokazuje fałszywe logowanie bez sesji i ciasteczek. Trasy pułapek są rejestrowane po
    wszystkich providerach, więc prawdziwa trasa o tym samym adresie wygrywa;
  - `HONEYPOT_FORMS`: formularz logowania ma ukryte pole i zaszyfrowany czas wyświetlenia
    (`x-admiro::honeypot`, `Security\Honeypot\FormHoneypot`). Bot (wypełnione pole, brak czasu, wysłanie
    szybciej niż 2 s) dostaje tę samą odpowiedź co człowiek, ale bez kodu;
  - **ban** (`IpBans`): od razu po POST na pułapkę albo po pułapce formularza, po GET dopiero po 5 wejściach
    w 10 minut. Trwa `HONEYPOT_BAN_MINUTES`; zbanowany adres dostaje 403 (z maskowaniem 404) na każdej trasie.
    W cache jest tylko hash IP, bez listy banów; `php artisan admiro:honeypot:unban {ip}` zdejmuje ban.
    Adresy z `HONEYPOT_WHITELIST` nigdy nie są banowane;
  - trafienia i bany zapisuje dziennik `honeypot` (`HONEYPOT_LOG_CHANNEL`): IP, ścieżka, user agent, podany
    login i tylko długość hasła.

## Zmienne środowiska

| Zmienna | Domyślnie | Znaczenie |
| --- | --- | --- |
| `APP_LOCALES` | `pl` | włączone języki, rozdzielone przecinkami |
| `ZONE_SESSION_LIFETIME` | `30` | minuty sesji w strefie z logowaniem |
| `TRUSTED_PROXIES` | brak | `*` albo adresy/sieci proxy |
| `SECURITY_HSTS`, `SECURITY_HSTS_MAX_AGE` | `true`, `31536000` | HSTS na HTTPS |
| `SECURITY_CSP` | `true` | Content-Security-Policy z nonce |
| `AUTH_CODE_TTL` | `10` | minuty ważności kodu z e-maila |
| `AUTH_PIN_MIN_LENGTH` | `4` | najkrótszy nowy PIN (cyfry, nie mniej niż 4) |
| `AUTH_PIN_MAX_LENGTH` | `16` | najdłuższy nowy PIN (cyfry, nie więcej niż 16); wpisany istniejący PIN jest sprawdzany w pełnym zakresie 4–16 |
| `AUTH_LAYOUT` | `centered` | układ stron logowania: `centered` (jedna karta), `split` (dwie karty obok siebie: marka po lewej) albo `split-reversed` (formularz po lewej, marka po prawej); strefa nadpisuje w `auth.layout` |
| `AUTH_TRUSTED_DEVICES` | `true` | zaufane urządzenia (strefa może je wyłączyć w configu) |
| `AUTH_TRUSTED_DEVICE_DAYS` | `30` | dni zaufania urządzenia |
| `SECURITY_MASK_ERRORS` | brak (produkcja) | maskowanie błędów jako 404 |
| `HONEYPOT_ROUTES`, `HONEYPOT_FORMS` | `false` | pułapki pod adresami botów i w formularzu logowania |
| `HONEYPOT_BAN_MINUTES` | `1440` | czas bana |
| `HONEYPOT_WHITELIST` | brak | adresy i sieci (CIDR) nigdy nie banowane |
| `HONEYPOT_LOG_CHANNEL` | `honeypot` | kanał dziennika pułapek |
| `AUDIT_RETENTION_DAYS` | `365` | dni przechowywania wpisów `audit_events` |
| `AUTH_LOG_CHANNEL` | `sign_in` | kanał dziennika logowań |

## Uprawnienia

- Model kont strefy (`ZoneAccount`) ma role i uprawnienia spatie w guardzie strefy, odmowy
  (`Permissions\Concerns\HasZonePermissions`, kontrakt `Contracts\HasZoneRoles`) i członkostwa
  w organizacjach (`organizations()`). Guard spatie wykrywa z providera strefy.
- **Organizacja żądania** (`Http\Middleware\UseCurrentTeam`, kontrakt `Contracts\CurrentOrganization`):
  strefa bez `teams` działa w organizacji operatora; strefa z `teams` w organizacji wybranej przez konto
  (sesja strefy, `account.organization.switch`), o ile nadal jest jego aktywnym członkostwem, inaczej
  w pierwszej. Bez członkostwa nie ma organizacji, więc nie ma uprawnień. Po odpowiedzi organizacja jest czyszczona.
  Layout strefy pokazuje przełącznik organizacji, gdy konto należy do więcej niż jednej (`$organizations`,
  `$currentOrganizationId` daje Core).
- **Role jako konteksty** (`role_contexts` w configu strefy z `teams`; kontrakt `Contracts\CurrentRole`,
  `Permissions\RoleSelector`): konto z kilkoma rolami w organizacji działa w jednej aktywnej roli — wybranej
  (`account.role.switch`, sesja strefy osobno dla każdej organizacji) albo pierwszej, zapamiętanej w sesji, więc rola
  nadana w trakcie sesji nie zmienia kontekstu sama. `UseCurrentTeam` ustawia ją razem z organizacją, a
  `HasZonePermissions::hasPermissionTo()` liczy wtedy tylko uprawnienia tej roli (plus bezpośrednie, odmowy nadal
  wygrywają); inne konta i inne organizacje nie są zawężane. Menu, pulpit i trasy strefy wynikają więc z aktywnej roli
  (np. właściciel, kierownik, sprzedawca). Przełącznik w layoucie: grupa „Rola” (`$roles`, `$currentRole`), gdy konto ma
  co najmniej dwie role.
- **Trasa z uprawnieniem:** `ZonePermission::middleware(Permission::ReportsView)` daje middleware
  `zone.permission:<uprawnienie>`, który sprawdza konto z guardu strefy przez `ZonePermissionChecker`
  (z odmowami); brak uprawnienia to 403. W kodzie: `ZonePermissionChecker::allows($zone, $account, $permission)`.
- **Rekordy organizacji:** model dziedziczący po `Models\OrganizationDataModel` wiąże się w trasie tylko
  w jednostce bieżącej organizacji — w kontekście departamentu w jego jednostce (`docs/baza-danych.md` §15,
  „Departamenty”); rekord innej jednostki to 404, zanim zadziała uprawnienie czy Policy.
- **Role i ich nadawanie:** pliki JSON, synchronizacja, nadawanie bez eskalacji i zakładanie właścicieli
  opisuje `docs/baza-danych.md` §15.
