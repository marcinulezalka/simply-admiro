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

namespace Simplysmart\Mainboard;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Translation\Loader;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Factory as ViewFinderFactory;
use Simplysmart\Mainboard\Console\SeedLocalization;
use Simplysmart\Mainboard\Contracts\InterfaceLocales;
use Simplysmart\Mainboard\Contracts\LocalFormatter;
use Simplysmart\Mainboard\Contracts\TextTranslator;
use Simplysmart\Mainboard\Listeners\LogSentMail;
use Simplysmart\Mainboard\Listeners\SetHistoryActorForJob;
use Simplysmart\Mainboard\Localization\DictionaryInterfaceLocales;
use Simplysmart\Mainboard\Localization\IntlLocalFormatter;
use Simplysmart\Mainboard\Logging\LogChannelDefaults;
use Simplysmart\Mainboard\Mail\MailLogDefaults;
use Simplysmart\Mainboard\Providers\AuditServiceProvider;
use Simplysmart\Mainboard\Providers\PermissionsServiceProvider;
use Simplysmart\Mainboard\Providers\SecurityServiceProvider;
use Simplysmart\Mainboard\Providers\SignInServiceProvider;
use Simplysmart\Mainboard\Providers\ZonesServiceProvider;
use Simplysmart\Mainboard\Translation\CascadeTextTranslator;
use Simplysmart\Mainboard\Translation\CoreTranslationPath;

/**
 * Punkt integracji paczki z aplikacją Laravel (auto-discovery przez extra.laravel.providers): config Core,
 * logi i poczta frameworka, teksty i lokalizacja, zasoby paczki (tłumaczenia, widoki, migracje), historia zmian
 * w jobach oraz providery obszarów (Providers\): strefy, logowanie, uprawnienia, dziennik zdarzeń, bezpieczeństwo.
 */
final class MainboardServiceProvider extends ServiceProvider
{
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
        $this->mergeConfigFrom(__DIR__ . '/../config/mainboard.php', 'mainboard');

        $config = $this->app->make(Repository::class);
        (new MailLogDefaults($config, $this->app->storagePath()))->apply();

        $logChannels = new LogChannelDefaults($config, $this->app->storagePath());
        $logChannels->define('mainboard.auth.log_channel', 'logs/sign-in.log');
        $logChannels->define('mainboard.honeypot.log_channel', 'logs/honeypot.log');

        $this->app->extend('translation.loader', fn(Loader $loader): Loader => (new CoreTranslationPath(
            $this->app->make(Filesystem::class),
            __DIR__ . '/../lang',
            $this->app->langPath(),
        ))->extend($loader));

        $this->app->bind(TextTranslator::class, CascadeTextTranslator::class);
        $this->app->bind(LocalFormatter::class, IntlLocalFormatter::class);
        $this->app->bind(InterfaceLocales::class, DictionaryInterfaceLocales::class);
        $this->useFlatFlagFallback($config);

        foreach (self::AREAS as $provider) {
            $this->app->register($provider);
        }
    }

    /**
     * Ładuje zasoby paczki po zarejestrowaniu wszystkich providerów. Widoki z `resources/views/app/` są
     * domyślnymi widokami aplikacji bez przestrzeni nazw (np. `welcome`): ten sam plik w `resources/views/`
     * aplikacji wygrywa, bo jej katalog jest przeszukiwany pierwszy.
     */
    public function boot(Dispatcher $events): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../lang', CascadeTextTranslator::CORE_NAMESPACE);
        $this->loadViewsFrom(__DIR__ . '/../resources/views', CascadeTextTranslator::CORE_NAMESPACE);
        $this->callAfterResolving('view', static function (ViewFinderFactory $views): void {
            $views->addLocation(__DIR__ . '/../resources/views/app');
        });
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $events->listen(MessageSent::class, LogSentMail::class);
        $events->listen(JobProcessing::class, [SetHistoryActorForJob::class, 'start']);
        $events->listen([JobProcessed::class, JobFailed::class], [SetHistoryActorForJob::class, 'finish']);

        if ($this->app->runningInConsole()) {
            $this->commands([SeedLocalization::class]);
        }
    }

    /**
     * Nieznana flaga (`x-mainboard::flag`) pokazuje flagę „xx” z wariantu flat (flag-icons, MIT) zamiast
     * domyślnej blade-flags (Twemoji, CC-BY-4.0) — tylko gdy aplikacja nie ustawiła własnej.
     */
    private function useFlatFlagFallback(Repository $config): void
    {
        if ($config->get(self::FLAG_FALLBACK_KEY, self::BLADE_FLAGS_FALLBACK) === self::BLADE_FLAGS_FALLBACK) {
            $config->set(self::FLAG_FALLBACK_KEY, self::FLAT_FLAG_FALLBACK);
        }
    }
}
