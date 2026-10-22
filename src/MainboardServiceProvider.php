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
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Factory as ViewFinderFactory;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Simplysmart\Mainboard\Auth\Devices\DeviceTokenIssuer;
use Simplysmart\Mainboard\Auth\Devices\EloquentTrustedDeviceRepository;
use Simplysmart\Mainboard\Auth\EloquentAccountLocator;
use Simplysmart\Mainboard\Auth\EloquentAccountStore;
use Simplysmart\Mainboard\Auth\Events\AccountAccessRevoked;
use Simplysmart\Mainboard\Auth\Events\AccountLocked;
use Simplysmart\Mainboard\Auth\Events\PinChanged;
use Simplysmart\Mainboard\Auth\Events\TrustedDeviceRegistered;
use Simplysmart\Mainboard\Auth\Events\TrustedDevicesRevoked;
use Simplysmart\Mainboard\Auth\Events\SignedOut;
use Simplysmart\Mainboard\Auth\Events\SignInRejected;
use Simplysmart\Mainboard\Auth\Events\SignInSucceeded;
use Simplysmart\Mainboard\Auth\Listeners\LogSignInActivity;
use Simplysmart\Mainboard\Auth\Listeners\RevokeTrustedDevicesOfAccount;
use Simplysmart\Mainboard\Auth\Listeners\SendAccountLockedMail;
use Simplysmart\Mainboard\Auth\Listeners\SendPinChangedMail;
use Simplysmart\Mainboard\Auth\Listeners\SendTrustedDeviceMails;
use Simplysmart\Mainboard\Auth\SignIn\EloquentLoginChallengeRepository;
use Simplysmart\Mainboard\Auth\SignIn\LoginCodeGenerator;
use Simplysmart\Mainboard\Logging\LogChannelDefaults;
use Simplysmart\Mainboard\Listeners\SetHistoryActorForJob;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Simplysmart\Mainboard\Console\PruneSignInChallenges;
use Simplysmart\Mainboard\Console\SeedLocalization;
use Simplysmart\Mainboard\Console\SeedOrganizations;
use Simplysmart\Mainboard\Console\SyncZonePermissions;
use Simplysmart\Mainboard\Console\UnbanIp;
use Simplysmart\Mainboard\Http\HoneypotRoutes;
use Simplysmart\Mainboard\Http\Middleware\BlockBannedIps;
use Simplysmart\Mainboard\Security\ErrorMasker;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Http\Kernel as FoundationKernel;
use Illuminate\Contracts\Foundation\CachesRoutes;
use Illuminate\Config\Repository as ConfigRepository;
use Simplysmart\Mainboard\Permissions\PermissionFiles;
use Simplysmart\Mainboard\Permissions\ZoneOwnerProvisioners;
use Simplysmart\Mainboard\Permissions\Events\ZoneRoleChanged;
use Simplysmart\Mainboard\Contracts\CurrentOrganization;
use Simplysmart\Mainboard\Contracts\ZonePermissionChecker;
use Simplysmart\Mainboard\Http\Middleware\RequireZonePermission;
use Simplysmart\Mainboard\Permissions\SpatieCurrentOrganization;
use Illuminate\Routing\Router;
use Simplysmart\Mainboard\Permissions\PermissionDefaults;
use Simplysmart\Mainboard\Permissions\SpatiePermissionChecker;
use Simplysmart\Mainboard\Contracts\AccountLocator;
use Simplysmart\Mainboard\Contracts\AccountStore;
use Simplysmart\Mainboard\Contracts\BlindIndex;
use Simplysmart\Mainboard\Contracts\LoginChallengeRepository;
use Simplysmart\Mainboard\Contracts\TrustedDeviceRepository;
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
use Illuminate\Support\ServiceProvider;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Contracts\LocalFormatter;
use Simplysmart\Mainboard\Contracts\InterfaceLocales;
use Simplysmart\Mainboard\Localization\DictionaryInterfaceLocales;
use Simplysmart\Mainboard\Contracts\SignInAccount;
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

    private const string ACCOUNT_VIEWS = CascadeTextTranslator::CORE_NAMESPACE . '::account.*';

    private const string FLAG_FALLBACK_KEY = 'blade-flags.fallback';

    private const string BLADE_FLAGS_FALLBACK = 'country-xx';

    private const string FLAT_FLAG_FALLBACK = 'flat-country-xx';

    private const string ZONE_LAYOUT_VIEW = CascadeTextTranslator::CORE_NAMESPACE . '::components.zone.layout';

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

        $logChannels = new LogChannelDefaults($this->app->make(Repository::class), $this->app->storagePath());
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

        $this->registerZones();
        $this->registerSignIn();
        $this->registerPermissions();
        $this->useFlatFlagFallback();
    }

    /**
     * Ładuje zasoby paczki po zarejestrowaniu wszystkich providerów. Widoki z `resources/views/app/` są
     * domyślnymi widokami aplikacji bez przestrzeni nazw (np. `welcome`): ten sam plik w `resources/views/`
     * aplikacji wygrywa, bo jej katalog jest przeszukiwany pierwszy.
     */
    public function boot(Dispatcher $events, TrustedProxies $trustedProxies, RateLimiter $limiter): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../lang', CascadeTextTranslator::CORE_NAMESPACE);
        $this->loadViewsFrom(__DIR__ . '/../resources/views', CascadeTextTranslator::CORE_NAMESPACE);
        $this->callAfterResolving('view', static function (ViewFinderFactory $views): void {
            $views->addLocation(__DIR__ . '/../resources/views/app');
        });
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        DB::prohibitDestructiveCommands($this->app->environment('production') === true);

        $events->listen(MessageSent::class, LogSentMail::class);
        $events->listen([
            SignInSucceeded::class,
            SignInRejected::class,
            AccountLocked::class,
            SignedOut::class,
            AccountAccessRevoked::class,
            PinChanged::class,
            TrustedDeviceRegistered::class,
            TrustedDevicesRevoked::class,
            ZoneRoleChanged::class,
        ], LogSignInActivity::class);
        $events->listen(AccountLocked::class, SendAccountLockedMail::class);
        $events->listen(PinChanged::class, SendPinChangedMail::class);
        $events->listen([AccountAccessRevoked::class, AccountLocked::class], RevokeTrustedDevicesOfAccount::class);
        $events->listen([TrustedDeviceRegistered::class, AccountAccessRevoked::class], SendTrustedDeviceMails::class);
        $events->listen(JobProcessing::class, [SetHistoryActorForJob::class, 'start']);
        $events->listen([JobProcessed::class, JobFailed::class], [SetHistoryActorForJob::class, 'finish']);

        $this->app->make(ViewFactory::class)->composer([self::SIGN_IN_VIEWS, self::ACCOUNT_VIEWS], function (View $view): void {
            $view->with('zone', $this->app->make(CurrentZone::class)->get());
        });
        $this->app->make(ViewFactory::class)->composer(self::ZONE_LAYOUT_VIEW, $this->shareZoneWithLayout(...));

        $this->app->make(Router::class)->aliasMiddleware(RequireZonePermission::ALIAS, RequireZonePermission::class);
        $limiter->for(SignInRoutes::THROTTLE, fn(Request $request): Limit => $this->signInRouteLimit($request));
        $limiter->for(HoneypotRoutes::THROTTLE, fn(Request $request): Limit => Limit::perMinute($this->app->make(ConfigRepository::class)->integer('mainboard.honeypot.route_throttle_per_minute'))->by((string) $request->ip()));
        $this->registerHoneypot();
        $this->app->afterResolving(ExceptionHandler::class, $this->renderExpiredFormsAsSignIn(...));

        if ($this->app->resolved(ExceptionHandler::class)) {
            $this->renderExpiredFormsAsSignIn($this->app->make(ExceptionHandler::class));
        }

        $this->app->afterResolving(ExceptionHandler::class, $this->maskErrors(...));

        if ($this->app->resolved(ExceptionHandler::class)) {
            $this->maskErrors($this->app->make(ExceptionHandler::class));
        }

        if ($this->app->runningInConsole()) {
            $this->commands([PruneSignInChallenges::class, SeedLocalization::class, SeedOrganizations::class, SyncZonePermissions::class, UnbanIp::class]);
            $this->app->afterResolving(Schedule::class, static function (Schedule $schedule): void {
                $schedule->command(PruneSignInChallenges::class)->hourly();
            });
        }

        $trustedProxies->apply();

        $this->useZoneRedirects();
        $this->app->afterResolving(HttpKernel::class, $this->useZoneRedirects(...));
    }

    /**
     * Nieznana flaga (`x-mainboard::flag`) pokazuje flagę „xx” z wariantu flat (flag-icons, MIT) zamiast
     * domyślnej blade-flags (Twemoji, CC-BY-4.0) — tylko gdy aplikacja nie ustawiła własnej.
     */
    private function useFlatFlagFallback(): void
    {
        $config = $this->app->make(Repository::class);

        if ($config->get(self::FLAG_FALLBACK_KEY, self::BLADE_FLAGS_FALLBACK) === self::BLADE_FLAGS_FALLBACK) {
            $config->set(self::FLAG_FALLBACK_KEY, self::FLAT_FLAG_FALLBACK);
        }
    }

    /**
     * Layout strefy dostaje strefę żądania i konto zalogowane w jej guardzie (albo null).
     */
    private function shareZoneWithLayout(View $view): void
    {
        $zone = $this->app->make(CurrentZone::class)->get();
        $account = $zone->requiresLogin() ? $this->app->make(AuthFactory::class)->guard($zone->guard)->user() : null;

        $view->with('zone', $zone)->with('account', $account instanceof SignInAccount ? $account : null);
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
     * Logowanie stref: konta, kody, zaufane urządzenia i stan logowania zapisywane w tabelach strefy,
     * indeks e-maila oraz kody i tokeny urządzeń jako HMAC z kluczem aplikacji.
     */
    private function registerSignIn(): void
    {
        $this->app->bind(AccountLocator::class, EloquentAccountLocator::class);
        $this->app->bind(AccountStore::class, EloquentAccountStore::class);
        $this->app->bind(LoginChallengeRepository::class, EloquentLoginChallengeRepository::class);
        $this->app->bind(TrustedDeviceRepository::class, EloquentTrustedDeviceRepository::class);
        $this->app->singleton(BlindIndex::class, fn(Application $app): BlindIndex => new HmacBlindIndex($this->appKey($app)));
        $this->app->singleton(LoginCodeGenerator::class, fn(Application $app): LoginCodeGenerator => new LoginCodeGenerator($this->appKey($app)));
        $this->app->singleton(DeviceTokenIssuer::class, fn(Application $app): DeviceTokenIssuer => new DeviceTokenIssuer($this->appKey($app)));
    }

    /**
     * Uprawnienia stref na spatie/laravel-permission: kontrakty sprawdzania i bieżącej organizacji, rejestr
     * plików ról (z katalogiem Core) i konfiguracja spatie ustawiana po register() wszystkich providerów
     * (wtedy config spatie jest już scalony), przed ich boot().
     */
    private function registerPermissions(): void
    {
        $this->app->bind(ZonePermissionChecker::class, SpatiePermissionChecker::class);
        $this->app->bind(CurrentOrganization::class, SpatieCurrentOrganization::class);
        $this->app->singleton(ZoneOwnerProvisioners::class);
        $this->app->singleton(PermissionFiles::class, static function (): PermissionFiles {
            $files = new PermissionFiles();
            $files->add(__DIR__ . '/../database/seeders/permissions');

            return $files;
        });
        $this->app->booting(function (): void {
            $this->app->make(PermissionDefaults::class)->apply();
        });
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
     * Maskowanie błędów (Security\ErrorMasker): odpowiedź o maskowanym statusie zastępuje zwykła strona 404.
     * Core ustawia callback respondUsing() handlera; aplikacja, która ustawi własny, wyłącza maskowanie Core.
     */
    private function maskErrors(ExceptionHandler $handler): void
    {
        if (! $handler instanceof Handler) {
            return;
        }

        $handler->respondUsing(function (Response $response, \Throwable $exception, Request $request) use ($handler): Response {
            if (! $this->app->make(ErrorMasker::class)->masks($response->getStatusCode())) {
                return $response;
            }

            return $handler->render($request, new NotFoundHttpException(previous: $exception));
        });
    }

    /**
     * Pułapki na boty, gdy włączone w `mainboard.honeypot`: globalna blokada zbanowanych adresów i (dla `routes`)
     * trasy pułapek rejestrowane po wszystkich providerach, żeby prawdziwe trasy wygrywały.
     */
    private function registerHoneypot(): void
    {
        $config = $this->app->make(ConfigRepository::class);

        if (! $config->boolean('mainboard.honeypot.routes') && ! $config->boolean('mainboard.honeypot.forms')) {
            return;
        }

        $kernel = $this->app->make(HttpKernel::class);

        if ($kernel instanceof FoundationKernel) {
            $kernel->prependMiddleware(BlockBannedIps::class);
        }

        if ($config->boolean('mainboard.honeypot.routes')) {
            $this->app->booted(function (): void {
                if (! ($this->app instanceof CachesRoutes && $this->app->routesAreCached())) {
                    $this->app->make(HoneypotRoutes::class)->register();
                }
            });
        }
    }

    /**
     * Wspólne mechanizmy stref: rejestr (singleton), strefa żądania (scoped) i przełącznik sesji
     * zapamiętujący bazowy config/session.php.
     */
    private function registerZones(): void
    {
        $this->app->singleton(ZoneRegistry::class, InMemoryZoneRegistry::class);
        $this->app->scoped(CurrentZone::class, RequestZone::class);
        $this->app->bind(ErrorMasker::class, fn(Application $app): ErrorMasker => new ErrorMasker(
            $app->make(ConfigRepository::class),
            $app->make(CurrentZone::class),
            $app->environment('production') === true,
        ));

        $this->app->extend('session', fn(): ZoneSessionManager => new ZoneSessionManager($this->app));

        $this->app->singleton(ZoneSessionConfigurator::class, fn(Application $app): ZoneSessionConfigurator => new ZoneSessionConfigurator(
            $app->make(Repository::class),
            $this->zoneSessionManager($app),
            (array) $app->make(Repository::class)->get('session', []),
            ! $app->environment('local'),
        ));
    }
}
