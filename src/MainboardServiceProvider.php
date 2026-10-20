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

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Cache\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Simplysmart\Mainboard\Auth\EloquentAccountLocator;
use Simplysmart\Mainboard\Auth\EloquentAccountStore;
use Simplysmart\Mainboard\Auth\Events\AccountLocked;
use Simplysmart\Mainboard\Auth\Events\SignedOut;
use Simplysmart\Mainboard\Auth\Events\SignInRejected;
use Simplysmart\Mainboard\Auth\Events\SignInSucceeded;
use Simplysmart\Mainboard\Auth\Listeners\LogSignInActivity;
use Simplysmart\Mainboard\Auth\Listeners\SendAccountLockedMail;
use Simplysmart\Mainboard\Auth\SignIn\EloquentLoginChallengeRepository;
use Simplysmart\Mainboard\Auth\SignIn\LoginCodeGenerator;
use Simplysmart\Mainboard\Auth\SignInLogDefaults;
use Simplysmart\Mainboard\Console\PruneSignInChallenges;
use Simplysmart\Mainboard\Contracts\AccountLocator;
use Simplysmart\Mainboard\Contracts\AccountStore;
use Simplysmart\Mainboard\Contracts\BlindIndex;
use Simplysmart\Mainboard\Contracts\LoginChallengeRepository;
use Simplysmart\Mainboard\Http\SignInRoutes;
use Simplysmart\Mainboard\Security\HmacBlindIndex;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
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
use Simplysmart\Mainboard\Zones\ZoneSessionManager;

/**
 * Punkt integracji paczki z aplikacją Laravel (auto-discovery przez extra.laravel.providers).
 *
 * Tu rejestrujemy wiązania kontraktów z src/Contracts/ oraz zasoby paczki: konfigurację
 * (mergeConfigFrom), migracje (loadMigrationsFrom), widoki (loadViewsFrom), tłumaczenia i trasy.
 */
final class MainboardServiceProvider extends ServiceProvider
{
    private const int PAGE_EXPIRED = 419;

    private const string SIGN_IN_VIEWS = CascadeTextTranslator::CORE_NAMESPACE . '::auth.*';

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

        (new SignInLogDefaults(
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
        $this->registerSignIn();
    }

    /**
     * Ładuje zasoby paczki po zarejestrowaniu wszystkich providerów.
     */
    public function boot(Dispatcher $events, TrustedProxies $trustedProxies, RateLimiter $limiter): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../lang', CascadeTextTranslator::CORE_NAMESPACE);
        $this->loadViewsFrom(__DIR__ . '/../resources/views', CascadeTextTranslator::CORE_NAMESPACE);
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        DB::prohibitDestructiveCommands($this->app->environment('production') === true);

        $events->listen(MessageSent::class, LogSentMail::class);
        $events->listen([SignInSucceeded::class, SignInRejected::class, AccountLocked::class, SignedOut::class], LogSignInActivity::class);
        $events->listen(AccountLocked::class, SendAccountLockedMail::class);

        $this->app->make(ViewFactory::class)->composer(self::SIGN_IN_VIEWS, function (View $view): void {
            $view->with('zone', $this->app->make(CurrentZone::class)->get());
        });

        $limiter->for(SignInRoutes::THROTTLE, fn(Request $request): Limit => $this->signInRouteLimit($request));
        $this->app->afterResolving(ExceptionHandler::class, $this->renderExpiredFormsAsSignIn(...));

        if ($this->app->resolved(ExceptionHandler::class)) {
            $this->renderExpiredFormsAsSignIn($this->app->make(ExceptionHandler::class));
        }

        if ($this->app->runningInConsole()) {
            $this->commands([PruneSignInChallenges::class]);
            $this->app->afterResolving(Schedule::class, static function (Schedule $schedule): void {
                $schedule->command(PruneSignInChallenges::class)->hourly();
            });
        }

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
     * Logowanie stref: konta, kody i stan logowania zapisywane w tabelach strefy, indeks e-maila
     * i kody HMAC z kluczem aplikacji.
     */
    private function registerSignIn(): void
    {
        $this->app->bind(AccountLocator::class, EloquentAccountLocator::class);
        $this->app->bind(AccountStore::class, EloquentAccountStore::class);
        $this->app->bind(LoginChallengeRepository::class, EloquentLoginChallengeRepository::class);
        $this->app->singleton(BlindIndex::class, fn(Application $app): BlindIndex => new HmacBlindIndex($this->appKey($app)));
        $this->app->singleton(LoginCodeGenerator::class, fn(Application $app): LoginCodeGenerator => new LoginCodeGenerator($this->appKey($app)));
    }

    /**
     * Menedżer sesji aplikacji (wiązanie `session`, zastąpione przez ZoneSessionManager).
     */
    private function zoneSessionManager(Application $app): ZoneSessionManager
    {
        $manager = $app->make('session');

        return $manager instanceof ZoneSessionManager ? $manager : throw new \LogicException('The session manager must be ' . ZoneSessionManager::class . '.');
    }

    private function appKey(Application $app): string
    {
        $key = $app->make(Repository::class)->get('app.key');

        return is_string($key) && $key !== '' ? $key : throw new \RuntimeException('APP_KEY is required for sign-in codes and the e-mail index.');
    }

    /**
     * Limit tras logowania: na parę strefa + IP.
     */
    private function signInRouteLimit(Request $request): Limit
    {
        $zone = $this->app->make(CurrentZone::class)->get();

        return Limit::perMinute($zone->auth()->routeThrottlePerMinute)->by($zone->name . '|' . $request->ip());
    }

    /**
     * Wygasły formularz (419) w strefie z logowaniem: powrót do logowania strefy z neutralnym komunikatem.
     */
    private function renderExpiredFormsAsSignIn(ExceptionHandler $handler): void
    {
        if (! $handler instanceof Handler) {
            return;
        }

        $handler->renderable(function (HttpExceptionInterface $exception): ?RedirectResponse {
            $zone = $this->app->make(CurrentZone::class);

            if ($exception->getStatusCode() !== self::PAGE_EXPIRED || ! $zone->has() || ! $zone->get()->requiresLogin()) {
                return null;
            }

            return $this->app->make(Redirector::class)
                ->route($zone->get()->routeName(ZoneRedirects::LOGIN_ROUTE))
                ->withErrors(['email' => text('sign_in.page_expired')]);
        });
    }

    /**
     * Wspólne mechanizmy stref: rejestr (singleton), strefa żądania (scoped) i przełącznik sesji
     * zapamiętujący bazowy config/session.php.
     */
    private function registerZones(): void
    {
        $this->app->singleton(ZoneRegistry::class, InMemoryZoneRegistry::class);
        $this->app->scoped(CurrentZone::class, RequestZone::class);

        $this->app->extend('session', static fn(SessionManager $manager, Application $app): ZoneSessionManager => new ZoneSessionManager($app));

        $this->app->singleton(ZoneSessionConfigurator::class, fn(Application $app): ZoneSessionConfigurator => new ZoneSessionConfigurator(
            $app->make(Repository::class),
            $this->zoneSessionManager($app),
            (array) $app->make(Repository::class)->get('session', []),
            ! $app->environment('local'),
        ));
    }
}
