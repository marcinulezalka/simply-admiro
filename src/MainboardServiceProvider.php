<?php

declare(strict_types=1);

namespace Simplysmart\Mainboard;

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Contracts\Translation\Loader;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Session\SessionManager;
use Illuminate\Support\ServiceProvider;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Contracts\LocalFormatter;
use Simplysmart\Mainboard\Contracts\TextTranslator;
use Simplysmart\Mainboard\Contracts\ZoneRegistry;
use Simplysmart\Mainboard\Listeners\LogSentMail;
use Simplysmart\Mainboard\Mail\MailLogDefaults;
use Simplysmart\Mainboard\Localization\IntlLocalFormatter;
use Simplysmart\Mainboard\Security\TrustedProxies;
use Simplysmart\Mainboard\Translation\CascadeTextTranslator;
use Simplysmart\Mainboard\Translation\CoreTranslationPath;
use Simplysmart\Mainboard\Zones\InMemoryZoneRegistry;
use Simplysmart\Mainboard\Zones\RequestZone;
use Simplysmart\Mainboard\Zones\ZoneRedirects;
use Simplysmart\Mainboard\Zones\ZoneSessionConfigurator;

/**
 * Punkt integracji paczki z aplikacją Laravel (auto-discovery przez extra.laravel.providers).
 *
 * Tu rejestrujemy wiązania kontraktów z src/Contracts/ oraz zasoby paczki: konfigurację
 * (mergeConfigFrom), migracje (loadMigrationsFrom), widoki (loadViewsFrom), tłumaczenia i trasy.
 */
final class MainboardServiceProvider extends ServiceProvider
{
    /**
     * Łączy konfigurację Core, uzupełnia nią konfigurację poczty i logów frameworka,
     * dokłada polskie tłumaczenia frameworka z lang/ paczki i wiąże kontrakty tekstów oraz formatowania.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/mainboard.php', 'mainboard');

        (new MailLogDefaults(
            $this->app->make(Repository::class),
            $this->app->storagePath(),
        ))->apply();

        $this->app->extend('translation.loader', fn(Loader $loader): Loader => (new CoreTranslationPath(
            $this->app->make(Filesystem::class),
            __DIR__ . '/../lang',
            $this->app->langPath(),
        ))->extend($loader));

        $this->app->bind(TextTranslator::class, CascadeTextTranslator::class);
        $this->app->bind(LocalFormatter::class, IntlLocalFormatter::class);

        $this->registerZones();
    }

    /**
     * Ładuje zasoby paczki po zarejestrowaniu wszystkich providerów.
     */
    public function boot(Dispatcher $events, TrustedProxies $trustedProxies): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../lang', CascadeTextTranslator::CORE_NAMESPACE);

        $events->listen(MessageSent::class, LogSentMail::class);

        $trustedProxies->apply();

        $this->useZoneRedirects();
        $this->app->afterResolving(HttpKernel::class, $this->useZoneRedirects(...));
    }

    /**
     * Przekierowania auth/guest według bieżącej strefy. Ustawiane także po rozwiązaniu kernela HTTP,
     * bo ApplicationBuilder::withMiddleware() ustawia wtedy własne (globalną trasę `login`).
     */
    private function useZoneRedirects(): void
    {
        Authenticate::redirectUsing(fn(): string => $this->app->make(ZoneRedirects::class)->guestRedirect());
        RedirectIfAuthenticated::redirectUsing(fn(): string => $this->app->make(ZoneRedirects::class)->userRedirect());
    }

    /**
     * Wspólne mechanizmy stref: rejestr (singleton), strefa żądania (scoped) i przełącznik sesji
     * zapamiętujący bazowy config/session.php.
     */
    private function registerZones(): void
    {
        $this->app->singleton(ZoneRegistry::class, InMemoryZoneRegistry::class);
        $this->app->scoped(CurrentZone::class, RequestZone::class);

        $this->app->singleton(ZoneSessionConfigurator::class, fn(Application $app): ZoneSessionConfigurator => new ZoneSessionConfigurator(
            $app->make(Repository::class),
            $app->make(SessionManager::class),
            (array) $app->make(Repository::class)->get('session', []),
            ! $app->environment('local'),
        ));
    }
}
