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

namespace Simplysmart\Admiro;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Translation\Loader;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\ServiceProvider;
use Simplysmart\Admiro\Wiki\Wiki;
use Simplysmart\Admiro\Console\SeedLocalization;
use Simplysmart\Admiro\Console\WriteCronScript;
use Simplysmart\Admiro\Contracts\InterfaceLocales;
use Simplysmart\Admiro\Contracts\LocalFormatter;
use Simplysmart\Admiro\Contracts\TextTranslator;
use Simplysmart\Admiro\Listeners\LogSentMail;
use Simplysmart\Admiro\Listeners\SetHistoryActorForJob;
use Simplysmart\Admiro\Localization\DictionaryInterfaceLocales;
use Simplysmart\Admiro\Localization\IntlLocalFormatter;
use Simplysmart\Admiro\Logging\LogChannelDefaults;
use Simplysmart\Admiro\Mail\MailLogDefaults;
use Simplysmart\Admiro\Providers\AuditServiceProvider;
use Simplysmart\Admiro\Providers\CookiesServiceProvider;
use Simplysmart\Admiro\Providers\CoreRoutesServiceProvider;
use Simplysmart\Admiro\Providers\DesignSystemServiceProvider;
use Simplysmart\Admiro\Providers\ExchangeRatesServiceProvider;
use Simplysmart\Admiro\Providers\DictionaryUpdatesServiceProvider;
use Simplysmart\Admiro\Providers\HolidaysServiceProvider;
use Simplysmart\Admiro\Providers\LegalServiceProvider;
use Simplysmart\Admiro\Providers\PermissionsServiceProvider;
use Simplysmart\Admiro\Providers\SecurityServiceProvider;
use Simplysmart\Admiro\Providers\SignInServiceProvider;
use Simplysmart\Admiro\Providers\ZonesServiceProvider;
use Simplysmart\Admiro\Translation\CascadeTextTranslator;
use Simplysmart\Admiro\Translation\CoreTranslationPath;

/**
 * Punkt integracji rdzenia z aplikacją Laravel (auto-discovery przez extra.laravel.providers, przed strefą
 * administratorów `Panel\PanelServiceProvider`): config paczki,
 * logi i poczta frameworka, teksty i lokalizacja, zasoby paczki (tłumaczenia, widoki, migracje), historia zmian
 * w jobach oraz providery obszarów (Providers\): strefy, logowanie, uprawnienia, dziennik zdarzeń, bezpieczeństwo, trasy Core, katalog design systemu.
 */
final class AdmiroServiceProvider extends ServiceProvider
{
    /**
     * Klucz configu paczki (`config('admiro.*')`, plik `config/admiro.php`).
     */
    public const string CONFIG = 'admiro';

    /**
     * Domyślne widoki aplikacji (np. `welcome`) i strony błędów (`errors/<kod>`): ostatnia ścieżka `view.paths`, więc
     * plik o tej samej nazwie w `resources/views/` aplikacji wygrywa, a Laravel znajduje tu strony błędów HTTP.
     */
    private const string APP_VIEWS = __DIR__ . '/../resources/views/app';

    /**
     * Providery obszarów Core, rejestrowane razem z tym providerem.
     *
     * @var list<class-string<ServiceProvider>>
     */
    private const array AREAS = [
        ZonesServiceProvider::class,
        SignInServiceProvider::class,
        PermissionsServiceProvider::class,
        AuditServiceProvider::class,
        SecurityServiceProvider::class,
        CoreRoutesServiceProvider::class,
        DesignSystemServiceProvider::class,
        ExchangeRatesServiceProvider::class,
        CookiesServiceProvider::class,
        LegalServiceProvider::class,
        HolidaysServiceProvider::class,
        DictionaryUpdatesServiceProvider::class,
    ];

    /**
     * Pojęcia słownika Core (Wiki\Wiki): klucz → ikona; teksty w lang/<locale>/wiki.php.
     */
    private const array WIKI = [
        'organizacja' => 'building',
        'organizacja-operatora' => 'shield-check',
        'departament' => 'sitemap',
        'strefa' => 'zones',
        'wlasciciel' => 'user',
    ];

    private const string FLAG_FALLBACK_KEY = 'blade-flags.fallback';

    private const string BLADE_FLAGS_FALLBACK = 'country-xx';

    private const string FLAT_FLAG_FALLBACK = 'flat-country-xx';

    /**
     * Łączy konfigurację Core, uzupełnia nią konfigurację poczty i logów frameworka, dokłada polskie tłumaczenia
     * frameworka z lang/ paczki, wiąże kontrakty tekstów, formatowania i języków oraz rejestruje providery obszarów.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/admiro.php', self::CONFIG);

        $config = $this->app->make(Repository::class);
        $viewPaths = $config->get('view.paths', []);
        $config->set('view.paths', [...(is_array($viewPaths) ? $viewPaths : []), self::APP_VIEWS]);
        (new MailLogDefaults($config, $this->app->storagePath()))->apply();

        $logChannels = new LogChannelDefaults($config, $this->app->storagePath());
        $logChannels->define('admiro.auth.log_channel', 'logs/sign-in.log');
        $logChannels->define('admiro.honeypot.log_channel', 'logs/honeypot.log');

        $this->app->extend('translation.loader', fn(Loader $loader): Loader => (new CoreTranslationPath(
            $this->app->make(Filesystem::class),
            __DIR__ . '/../lang',
            $this->app->langPath(),
        ))->extend($loader));

        $this->app->bind(TextTranslator::class, CascadeTextTranslator::class);
        $this->app->bind(LocalFormatter::class, IntlLocalFormatter::class);
        $this->app->bind(InterfaceLocales::class, DictionaryInterfaceLocales::class);
        $this->app->singleton(Wiki::class, static function (): Wiki {
            $wiki = new Wiki();

            foreach (self::WIKI as $key => $icon) {
                $wiki->add($key, $icon);
            }

            return $wiki;
        });
        $this->useFlatFlagFallback($config);

        foreach (self::AREAS as $provider) {
            $this->app->register($provider);
        }
    }

    /**
     * Ładuje zasoby paczki po zarejestrowaniu wszystkich providerów (domyślne widoki aplikacji i strony błędów
     * dodaje register() przez `view.paths`, zob. APP_VIEWS).
     */
    public function boot(Dispatcher $events): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../lang', CascadeTextTranslator::CORE_NAMESPACE);
        $this->loadViewsFrom(__DIR__ . '/../resources/views', CascadeTextTranslator::CORE_NAMESPACE);
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $events->listen(MessageSent::class, LogSentMail::class);
        $events->listen(JobProcessing::class, [SetHistoryActorForJob::class, 'start']);
        $events->listen([JobProcessed::class, JobFailed::class], [SetHistoryActorForJob::class, 'finish']);

        if ($this->app->runningInConsole()) {
            $this->commands([SeedLocalization::class, WriteCronScript::class]);
        }
    }

    /**
     * Nieznana flaga (`x-admiro::flag`) pokazuje flagę „xx” z wariantu flat (flag-icons, MIT) zamiast
     * domyślnej blade-flags (Twemoji, CC-BY-4.0) — tylko gdy aplikacja nie ustawiła własnej.
     */
    private function useFlatFlagFallback(Repository $config): void
    {
        if ($config->get(self::FLAG_FALLBACK_KEY, self::BLADE_FLAGS_FALLBACK) === self::BLADE_FLAGS_FALLBACK) {
            $config->set(self::FLAG_FALLBACK_KEY, self::FLAT_FLAG_FALLBACK);
        }
    }
}
