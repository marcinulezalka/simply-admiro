<?php

declare(strict_types=1);

/**
 * This file is part of simplysmart/admiro.
 *
 * Copyright (c) 2026 Marcin Ulezalka <marcin.ulezalka@simplysmart.pl>
 *
 * Licensed under the Simplysmart Restricted Source License 1.0.
 * See the LICENSE file distributed with this source code.
 */

use Simplysmart\Admiro\Auth\PinLength;
use Simplysmart\Admiro\Enums\AuthLayout;
use Simplysmart\Admiro\Models\AdmiroAccount;
use Simplysmart\Admiro\Panel\PanelServiceProvider;

/**
 * Liczba całkowita ze zmiennej środowiska; brak albo niepoprawna wartość daje wartość domyślną.
 */
$envInt = static fn(string $key, int $default): int => filter_var(env($key), FILTER_VALIDATE_INT, ['options' => ['default' => $default]]);

/**
 * Tekst ze zmiennej środowiska; brak albo wartość nieskalarna daje wartość domyślną.
 */
$envString = static fn(string $key, string $default): string => is_scalar($value = env($key, $default)) ? (string) $value : $default;

// Fonty logowania (latin i latin-ext z polskimi literami; licencja SIL OFL w public/fonts/).
$latin = 'U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0304,U+0308,U+0329,U+2000-206F,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD';
$latinExt = 'U+0100-02BA,U+02BD-02C5,U+02C7-02CC,U+02CE-02D7,U+02DD-02FF,U+0304,U+0308,U+0329,U+1D00-1DBF,U+1E00-1E9F,U+1EF2-1EFF,U+2020,U+20A0-20AB,U+20AD-20C0,U+2113,U+2C60-2C7F,U+A720-A7FF';

$fonts = [];

foreach ([['space-grotesk', 'Space Grotesk', 600, 'normal'], ['instrument-serif', 'Instrument Serif', 400, 'italic']] as [$file, $family, $weight, $style]) {
    foreach (['latin' => $latin, 'latin-ext' => $latinExt] as $subset => $range) {
        $fonts["{$file}-{$subset}-{$weight}-{$style}"] = [
            'family' => $family,
            'weight' => $weight,
            'style' => $style,
            'file' => __DIR__ . "/../public/fonts/{$file}-{$subset}-{$weight}-{$style}.woff2",
            'unicode_range' => $range,
        ];
    }
}

return [

    'mail' => [

        /*
         * Kanał logów dla poczty: mailer "log" zapisuje tu treść wiadomości, a listener
         * LogSentMail odnotowuje każdą wysyłkę przez prawdziwy transport (SMTP itp.).
         */
        'log_channel' => env('MAIL_LOG_CHANNEL', 'mail'),

        /*
         * Liczba dni przechowywania dziennych plików storage/logs/email-RRRR-MM-DD.log.
         */
        'log_days' => $envInt('LOG_DAILY_DAYS', 14),

        /*
         * Odmowa wysyłki bez szyfrowania: przy schemacie "smtp" (port 587) STARTTLS staje się
         * obowiązkowy zamiast „jeśli się da”. Schemat "smtps" (port 465) i tak szyfruje od początku.
         */
        'require_tls' => (bool) env('MAIL_REQUIRE_TLS', true),

    ],

    'organization_brand' => [

        /*
         * Dysk plików marki organizacji (logo, sygnet, ikona strony) zapisywanych w panelu. Prywatny: pliki
         * czytają e-maile (osadzone w wiadomości) i podgląd w panelu, nie serwuje ich publiczny adres.
         */
        'disk' => env('ADMIRO_ORGANIZATION_BRAND_DISK', 'local'),

    ],

    'locale' => [

        /*
         * Waluta (kod ISO 4217) dla local_money(), gdy wywołanie nie poda własnej.
         */
        'currency' => env('APP_CURRENCY', 'PLN'),

        /*
         * Włączone języki (kody rozdzielone przecinkami). Jeden język: trasy bez prefiksu {locale};
         * kilka: prefiks {locale} zawsze obecny, ograniczony do tej listy (Localization\LocaleRouting).
         */
        'enabled' => array_values(array_filter(array_map(trim(...), explode(',', $envString('APP_LOCALES', 'pl'))))),

    ],

    /*
     * Kursy walut w złotych z tabeli A NBP (Web API): adres API i limit czasu zapytania, ile dni wstecz pobiera
     * codzienna synchronizacja (uzupełnia braki po świętach i awariach) i godzina jej uruchamiania w dni robocze
     * (NBP publikuje tabelę A ok. 11:45–12:15; pusta wartość wyłącza harmonogram).
     */
    'exchange_rates' => [
        'nbp_url' => env('NBP_API_URL', 'https://api.nbp.pl/api'),
        'timeout_seconds' => is_numeric($timeout = env('NBP_API_TIMEOUT')) ? (int) $timeout : 15,
        'days_back' => is_numeric($days = env('EXCHANGE_RATES_DAYS_BACK')) ? (int) $days : 7,
        'schedule_at' => $envString('EXCHANGE_RATES_SCHEDULE_AT', '12:30'),
    ],

    /*
     * Święta i dni wolne krajów z publicznego API Nager.Date (adres i limit czasu zapytania) oraz comiesięczna
     * synchronizacja dla aktywnych krajów na bieżący i następny rok.
     */
    'holidays' => [
        'nager_url' => env('HOLIDAYS_API_URL', 'https://date.nager.at/api/v3'),
        'timeout_seconds' => is_numeric($timeout = env('HOLIDAYS_API_TIMEOUT')) ? (int) $timeout : 15,
        'schedule' => (bool) env('HOLIDAYS_SCHEDULE', true),
    ],

    /*
     * Zgoda na cookies: baner i ustawienia cookies na każdej stronie (gdy opublikowano politykę cookies albo paczka
     * zarejestrowała skrypt opcjonalny), nazwa i ważność ciasteczka zgody, limit zapisów zgód na minutę z jednego
     * adresu i czas przechowywania rejestru zgód (rozliczalność RODO, domyślnie 5 lat).
     */
    'cookies' => [
        'enabled' => (bool) env('COOKIE_CONSENT', true),
        'cookie_name' => $envString('COOKIE_CONSENT_NAME', 'ss_consent'),
        'lifetime_days' => is_numeric($days = env('COOKIE_CONSENT_DAYS')) ? (int) $days : 365,
        'throttle_per_minute' => 30,
        'retention_days' => is_numeric($days = env('COOKIE_CONSENT_RETENTION_DAYS')) ? (int) $days : 1826,
    ],

    /*
     * Dokumenty prawne (polityka prywatności, klauzula RODO, polityka cookies): administrator danych wpisywany do treści
     * pierwszych wersji przez seeder `admiro:legal:seed` (imię i nazwisko albo nazwa i e-mail kontaktowy, bez adresu).
     * Kolejne wersje zmienia się w panelu administratora, nie w configu.
     */
    'legal' => [
        'controller' => [
            'name' => $envString('LEGAL_CONTROLLER_NAME', 'Marcin Ulężałka'),
            'email' => $envString('LEGAL_CONTROLLER_EMAIL', 'kontakt@simplysmart.pl'),
        ],
    ],

    'localization' => [

        /*
         * Kraj aplikacji (ISO 3166-1 alpha-2), gdy słownik krajów nie ma jeszcze kraju domyślnego
         * (is_default). Język i waluta domyślne biorą się z app.locale i locale.currency.
         */
        'country' => env('APP_COUNTRY', 'PL'),

        /*
         * Stawka VAT (procent), gdy kraj domyślny nie ma w słowniku stawki podstawowej na dany dzień.
         */
        'vat_percent' => $envString('APP_DEFAULT_VAT', '23'),

    ],

    'auth' => [

        /*
         * Logowanie stref z kontami: e-mail → kod z e-maila → PIN (przy pierwszym logowaniu ustawienie PIN-u).
         * Strefa nadpisuje dowolny klucz w swoim configu (`zone.auth`); listy `steps`
         * i `pin.lockout_minutes` zastępuje w całości.
         */
        'steps' => [
            Simplysmart\Admiro\Auth\SignIn\Steps\EmailCodeStep::class,
            Simplysmart\Admiro\Auth\SignIn\Steps\PinSetupStep::class,
            Simplysmart\Admiro\Auth\SignIn\Steps\PinStep::class,
        ],

        'code' => [
            'length' => 6,
            'ttl_minutes' => $envInt('AUTH_CODE_TTL', 10),
            'max_attempts' => 5,
            'resend_cooldown_seconds' => 60,
        ],

        'pin' => [
            /*
             * Dopuszczalna długość nowego PIN-u (cyfry), zakres w granicach 4–16 (Auth\PinLength).
             * Wpisany istniejący PIN jest sprawdzany w pełnym zakresie 4–16, więc zmiana nikogo nie blokuje.
             */
            'min_length' => $envInt('AUTH_PIN_MIN_LENGTH', PinLength::MIN),
            'max_length' => $envInt('AUTH_PIN_MAX_LENGTH', PinLength::MAX),
            'stage_ttl_minutes' => 5,
            'max_attempts' => 5,

            /*
             * Czas kolejnych blokad po błędnych PIN-ach (minuty); ostatni powtarza się. Licznik i poziom
             * blokady zeruje dopiero udane logowanie, więc po blokadzie każdy błędny PIN blokuje dłużej.
             */
            'lockout_minutes' => [15, 60, 1440],
        ],

        /*
         * Limity prób (max w decay_minutes), klucze cache z prefiksem strefy.
         */
        'limits' => [
            'code_requests' => ['max' => 5, 'decay_minutes' => 15],
            'account_code_requests' => ['max' => 10, 'decay_minutes' => 60],
            'code_checks' => ['max' => 10, 'decay_minutes' => 1],
            'account_code_checks' => ['max' => 20, 'decay_minutes' => 60],
            'pin_checks' => ['max' => 10, 'decay_minutes' => 1],
            'pin_changes' => ['max' => 5, 'decay_minutes' => 15],
        ],

        /*
         * Zaufane urządzenia: po kodzie z e-maila można zapamiętać przeglądarkę na `lifetime_days` dni.
         * Wtedy logowanie pomija kod (czynnikiem posiadania jest urządzenie), ale zawsze wymaga PIN-u.
         * Strefa wymagająca kodu zawsze ustawia `enabled` na false.
         */
        'trusted_devices' => [
            'enabled' => (bool) env('AUTH_TRUSTED_DEVICES', true),
            'lifetime_days' => $envInt('AUTH_TRUSTED_DEVICE_DAYS', 30),
            'max_per_account' => 10,
        ],

        /*
         * Układ stron logowania (Enums\AuthLayout): `centered` — jedna karta na środku, `split` — dwie karty
         * obok siebie (marka po lewej, nagłówek i formularz po prawej), `split-reversed` — to samo w odbiciu
         * (formularz po lewej, marka po prawej). Strefa może go nadpisać w `auth.layout`.
         */
        'layout' => env('AUTH_LAYOUT', AuthLayout::Centered->value),

        /*
         * Baner w karcie marki układu dwóch kart zamiast logo (Auth\AuthBrand): strefa podaje w `auth.brand`
         * ścieżki plików `light` (i opcjonalnie `dark`) oraz klucz tekstu `alt`; null = logo.
         */
        'brand' => null,

        /*
         * Logo strefy w karcie marki układu dwóch kart zamiast logo simplysmart: strefa podaje w `auth.logo`
         * ścieżkę pliku SVG z fill="currentColor" (osadzany inline, kolor z karty); null = logo simplysmart.
         */
        'logo' => null,

        'route_throttle_per_minute' => 20,

        'challenge_prune_hours' => 24,

        /*
         * Kanał dziennika logowań; Core tworzy go (storage/logs/sign-in.log), gdy aplikacja go nie zdefiniowała.
         */
        'log_channel' => env('AUTH_LOG_CHANNEL', 'sign_in'),

    ],

    'audit' => [

        /*
         * Ile dni trzymać wpisy dziennika zdarzeń (`audit_events`); starsze usuwa codziennie `admiro:audit:prune`.
         */
        'retention_days' => $envInt('AUDIT_RETENTION_DAYS', 365),

    ],

    'cron' => [

        /*
         * Skrypt crona `storage/app/cron.sh` (harmonogram i worker kolejki dla hostingu bez supervisora), tworzony
         * komendą `admiro:cron:script`. Wartości trafiają do skryptu przy generowaniu; sekretów nie zawiera.
         * `php` to polecenie PHP na serwerze (nazwa albo ścieżka), `interval_minutes` to interwał zadania w panelu
         * hostingu (worker kończy pracę 10 s przed kolejnym uruchomieniem), `queue_worker` = false pomija worker.
         */
        'php' => env('CRON_PHP', 'php'),
        'interval_minutes' => $envInt('CRON_INTERVAL_MINUTES', 1),
        'queue_tries' => $envInt('CRON_QUEUE_TRIES', 3),
        'queue_worker' => (bool) env('CRON_QUEUE_WORKER', true),

    ],

    'honeypot' => [

        /*
         * Pułapki na boty, domyślnie wyłączone. `routes`: fałszywe logowanie pod adresami sprawdzanymi przez
         * boty (`paths`, bez sesji i ciasteczek; prawdziwe trasy zawsze wygrywają). `forms`: ukryte pole
         * i zaszyfrowany czas wyświetlenia formularza logowania (wysłanie szybciej niż `form_min_seconds`
         * to bot). Ban: od razu po POST na pułapkę albo wypełnionym ukrytym polu, po `get_hits_threshold`
         * wejściach GET w `get_hits_window_minutes`; trwa `ban_minutes`. W cache tylko hash IP.
         */
        'routes' => (bool) env('HONEYPOT_ROUTES', false),
        'forms' => (bool) env('HONEYPOT_FORMS', false),

        'paths' => [
            'login', 'admin', 'admin/{any}', 'administrator', 'administrator/{any}', 'wp-login.php', 'wp-admin',
            'wp-admin/{any}', 'xmlrpc.php', 'phpmyadmin', 'phpmyadmin/{any}', 'pma', 'user/login', 'backend',
            'cms', '.env', '.git/{any}', 'config.php',
        ],

        'form_min_seconds' => 2,
        'ban_minutes' => $envInt('HONEYPOT_BAN_MINUTES', 1440),
        'get_hits_threshold' => 5,
        'get_hits_window_minutes' => 10,
        'route_throttle_per_minute' => 60,

        /*
         * Adresy i sieci (CIDR), które nigdy nie są banowane, rozdzielone przecinkami.
         */
        'whitelist' => array_values(array_filter(array_map(trim(...), explode(',', $envString('HONEYPOT_WHITELIST', ''))))),

        'log_channel' => env('HONEYPOT_LOG_CHANNEL', 'honeypot'),

    ],

    'zones' => [

        /*
         * Czas życia sesji (w minutach) strefy z logowaniem, gdy strefa nie poda własnego.
         */
        'session_lifetime' => $envInt('ZONE_SESSION_LIFETIME', 30),

        /*
         * Nagłówek X-Robots-Tag stref z logowaniem: bez indeksowania i bez trenowania AI.
         */
        'robots' => 'noindex, nofollow, noarchive, nosnippet, noimageindex, noai, noimageai',

    ],

    /*
     * Strona design systemu `/design-system` (trasa `simplysmart.design-system`): tokeny i komponenty Core na żywo.
     * Domyślnie włączona poza produkcją; wyłączona odpowiada 404.
     */
    'design_system' => [
        'enabled' => (bool) env('DESIGN_SYSTEM_PAGE', env('APP_ENV', 'production') !== 'production'),
    ],

    /*
     * Strona startowa aplikacji (widok `welcome`): adres, do którego prowadzi logo simplysmart w karcie marki
     * (nowa karta); logo stref prowadzą do `website` swoich stref.
     */
    'welcome' => [
        'website' => $envString('WELCOME_WEBSITE', 'https://simplysmart.pl'),
    ],

    'security' => [

        /*
         * Zaufane proxy: "*" albo adresy/sieci rozdzielone przecinkami; null = brak (IP z połączenia).
         * Bez tego za load balancerem wszyscy mają adres proxy, więc dzielą limity prób logowania.
         */
        'trusted_proxies' => env('TRUSTED_PROXIES'),

        /*
         * Na produkcji Core blokuje destrukcyjne komendy bazy (migrate:fresh, migrate:refresh, migrate:reset, db:wipe).
         * true zdejmuje blokadę: ustawia ją tylko `deploy:fresh` z admiro dla jednego procesu migrate:fresh
         * (zmienna środowiska przed komendą, config bez cache). Nigdy nie wpisuj jej do .env.
         */
        'allow_destructive_commands' => (bool) env('ADMIRO_ALLOW_DESTRUCTIVE_COMMANDS', false),

        /*
         * Strict-Transport-Security dla żądań HTTPS.
         */
        'hsts' => [
            'enabled' => (bool) env('SECURITY_HSTS', true),
            'max_age' => $envInt('SECURITY_HSTS_MAX_AGE', 31536000),
        ],

        /*
         * Content-Security-Policy z nonce ({nonce}) przekazywanym też do Vite; pomijana,
         * gdy działa serwer deweloperski Vite (hot).
         */
        'csp' => [
            'enabled' => (bool) env('SECURITY_CSP', true),
            'policy' => "default-src 'self'; script-src 'self' 'nonce-{nonce}'; style-src 'self' 'nonce-{nonce}'; "
                . "img-src 'self' data:; font-src 'self'; connect-src 'self'; object-src 'none'; "
                . "base-uri 'self'; form-action 'self'; frame-ancestors 'none'",
        ],

        /*
         * Permissions-Policy: funkcje przeglądarki wyłączone we wszystkich strefach.
         */
        'permissions_policy' => 'camera=(), microphone=(), geolocation=(), payment=(), usb=()',

        /*
         * Maskowanie błędów: odpowiedzi o tych statusach wyglądają jak 404, więc odwiedzający nie dowie się,
         * że strona istnieje albo że nie ma do niej dostępu. null = tylko na produkcji. Strefa może
         * nadpisać `mask_errors` w swoim configu (`security.mask_errors`). 503 (konserwacja) nie jest maskowany.
         */
        'mask_errors' => env('SECURITY_MASK_ERRORS') === null || env('SECURITY_MASK_ERRORS') === ''
            ? null
            : (bool) env('SECURITY_MASK_ERRORS'),
        'masked_statuses' => [401, 403, 405, 419, 429],

        /*
         * Agenci AI (fragmenty User-Agent), którzy dostają 403 w strefach z `security.block_ai_agents`
         * (domyślnie strefy z logowaniem), niezależnie od X-Robots-Tag.
         */
        'ai_agents' => [
            'GPTBot', 'ChatGPT-User', 'OAI-SearchBot',
            'ClaudeBot', 'Claude-User', 'Claude-SearchBot', 'Claude-Web', 'anthropic-ai',
            'GoogleOther', 'Google-Extended',
            'PerplexityBot', 'Perplexity-User',
            'meta-externalagent', 'meta-externalfetcher', 'FacebookBot',
            'CCBot', 'Bytespider', 'Amazonbot', 'cohere-ai', 'cohere-training-data-crawler', 'Diffbot',
            'DuckAssistBot', 'MistralAI-User', 'YouBot', 'AI2Bot', 'Ai2Bot-Dolma', 'ImagesiftBot',
            'Timpibot', 'omgili', 'PanguBot', 'Kangaroo Bot', 'Webzio-Extended',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Strefa administratorów
    |--------------------------------------------------------------------------
    |
    | Definicja strefy dla mechanizmu stref (docs/strefy.md). Slug to tylko segment URL:
    | jego zmiana nie zmienia nazw tras (prefiks "admiro." jest stały) i nie może być kodem języka.
    | Administratorzy domyślnie zawsze logują się kodem z e-maila i PIN-em (bez zaufanych urządzeń).
    | PIN administratorów ma co najmniej 8 cyfr zamiast domyślnych 4 (ADMIRO_PIN_MIN_LENGTH); maksimum 16 z auth.pin.
    | Logowanie w dwóch kartach: formularz po lewej, po prawej ciemna karta marki z logotypem „admiro” na środku.
    | Marka strefy (public/images/, źródła w brand/): logotyp „admiro” w kroju Zen Dots ze złotą koroną nad „i”
    | na logowaniu i w e-mailach, złota korona z dolną kreską jako awatar i ikony strony.
    | E-maile strefy we własnym layoucie (resources/views/mail/admiro/): karta ze złotym paskiem korony u góry, logo
    | w nagłówku karty i kody w osobnych polach cyfr, w grafitowej palecie strefy (`mail.palette`).
    | Tytuł logowania z tekstów strefy (sign_in.admiro.* w lang/<locale>/sign_in.php) w kroju Space Grotesk, wyróżnione słowo
    | kursywą Instrument Serif (fonty w public/fonts/, przypisane tokenom w public/css/admiro.css).
    |
    */

    'zone' => [
        'name' => 'admiro',
        'slug' => env('ADMIRO_SLUG', 'admiro'),
        'model' => AdmiroAccount::class,
        'auth' => [
            'trusted_devices' => ['enabled' => (bool) env('ADMIRO_TRUSTED_DEVICES', false)],
            'pin' => ['min_length' => filter_var(env('ADMIRO_PIN_MIN_LENGTH'), FILTER_VALIDATE_INT, ['options' => ['default' => 8]])],
            'layout' => 'split-reversed',
            'logo' => __DIR__ . '/../public/images/logo.svg',
        ],
        'website' => $envString('ADMIRO_WEBSITE', 'https://admiro.simplysmart.pl'),
        'brand' => [
            'logo' => __DIR__ . '/../public/images/logo.png',
            'signet' => __DIR__ . '/../public/images/signet.png',
        ],
        'mail' => [
            'views' => 'admiro::mail.admiro',
            'palette' => ['bg' => '#f4f4f5', 'surface' => '#ffffff', 'border' => '#e4e4e7', 'text' => '#18181b', 'muted' => '#71717a', 'accent' => '#3f3f46', 'on_accent' => '#ffffff'],
        ],
        'stylesheets' => [__DIR__ . '/../public/css/admiro.css'],
        'fonts' => $fonts,
        'legal_documents' => ['privacy', 'gdpr'],
        'texts' => PanelServiceProvider::NAMESPACE,
        'favicon' => [
            'ico' => __DIR__ . '/../public/images/favicon.ico',
            'svg' => __DIR__ . '/../public/images/favicon.svg',
            'apple' => __DIR__ . '/../public/images/apple-touch-icon.png',
        ],
    ],

];
