<?php

declare(strict_types=1);

/**
 * Konfiguracja Core (simplysmart/mainboard), łączona przez MainboardServiceProvider::register().
 *
 * Wartości nadpisuje się zmiennymi środowiska; pliku nie publikuje się do szkieletu.
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

    ],

];
