<?php

declare(strict_types=1);

/**
 * This file is part of simplysmart/mainboard.
 *
 * Copyright (c) 2026 Marcin Ulezalka <marcin.ulezalka@simplysmart.pl>
 *
 * Licensed under the Simplysmart Restricted Source License 1.0.
 * See the LICENSE file distributed with this source code.
 */

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
        'log_days' => (int) env('LOG_DAILY_DAYS', 14),

        /*
         * Odmowa wysyłki bez szyfrowania: przy schemacie "smtp" (port 587) STARTTLS staje się
         * obowiązkowy zamiast „jeśli się da”. Schemat "smtps" (port 465) i tak szyfruje od początku.
         */
        'require_tls' => (bool) env('MAIL_REQUIRE_TLS', true),

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
        'enabled' => array_values(array_filter(array_map(trim(...), explode(',', (string) env('APP_LOCALES', 'pl'))))),

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
        'vat_percent' => (string) env('APP_DEFAULT_VAT', '23'),

    ],

    'auth' => [

        /*
         * Logowanie stref z kontami: e-mail → kod z e-maila → PIN (przy pierwszym logowaniu ustawienie PIN-u).
         * Strefa nadpisuje dowolny klucz w swoim configu (`zone.auth`); listy `steps`
         * i `pin.lockout_minutes` zastępuje w całości.
         */
        'steps' => [
            Simplysmart\Mainboard\Auth\SignIn\Steps\EmailCodeStep::class,
            Simplysmart\Mainboard\Auth\SignIn\Steps\PinSetupStep::class,
            Simplysmart\Mainboard\Auth\SignIn\Steps\PinStep::class,
        ],

        'code' => [
            'length' => 6,
            'ttl_minutes' => (int) env('AUTH_CODE_TTL', 10),
            'max_attempts' => 5,
            'resend_cooldown_seconds' => 60,
        ],

        'pin' => [
            'length' => (int) env('AUTH_PIN_LENGTH', 6),
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
            'lifetime_days' => (int) env('AUTH_TRUSTED_DEVICE_DAYS', 30),
            'max_per_account' => 10,
        ],

        'route_throttle_per_minute' => 20,

        'challenge_prune_hours' => 24,

        /*
         * Kanał dziennika logowań; Core tworzy go (storage/logs/sign-in.log), gdy aplikacja go nie zdefiniowała.
         */
        'log_channel' => env('AUTH_LOG_CHANNEL', 'sign_in'),

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
        'ban_minutes' => (int) env('HONEYPOT_BAN_MINUTES', 1440),
        'get_hits_threshold' => 5,
        'get_hits_window_minutes' => 10,
        'route_throttle_per_minute' => 60,

        /*
         * Adresy i sieci (CIDR), które nigdy nie są banowane, rozdzielone przecinkami.
         */
        'whitelist' => array_values(array_filter(array_map(trim(...), explode(',', (string) env('HONEYPOT_WHITELIST', ''))))),

        'log_channel' => env('HONEYPOT_LOG_CHANNEL', 'honeypot'),

    ],

    'zones' => [

        /*
         * Czas życia sesji (w minutach) strefy z logowaniem, gdy strefa nie poda własnego.
         */
        'session_lifetime' => (int) env('ZONE_SESSION_LIFETIME', 30),

        /*
         * Nagłówek X-Robots-Tag stref z logowaniem: bez indeksowania i bez trenowania AI.
         */
        'robots' => 'noindex, nofollow, noarchive, nosnippet, noimageindex, noai, noimageai',

    ],

    'security' => [

        /*
         * Zaufane proxy: "*" albo adresy/sieci rozdzielone przecinkami; null = brak (IP z połączenia).
         * Bez tego za load balancerem wszyscy mają adres proxy, więc dzielą limity prób logowania.
         */
        'trusted_proxies' => env('TRUSTED_PROXIES'),

        /*
         * Strict-Transport-Security dla żądań HTTPS.
         */
        'hsts' => [
            'enabled' => (bool) env('SECURITY_HSTS', true),
            'max_age' => (int) env('SECURITY_HSTS_MAX_AGE', 31536000),
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

];
