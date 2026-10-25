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

namespace Simplysmart\Admiro\Providers;

use Illuminate\Cache\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\ServiceProvider;
use RuntimeException;
use Simplysmart\Admiro\Auth\Devices\DeviceTokenIssuer;
use Simplysmart\Admiro\Auth\Devices\EloquentTrustedDeviceRepository;
use Simplysmart\Admiro\Auth\EloquentAccountLocator;
use Simplysmart\Admiro\Auth\EloquentAccountStore;
use Simplysmart\Admiro\Auth\Events\AccountAccessRevoked;
use Simplysmart\Admiro\Auth\Events\AccountLocked;
use Simplysmart\Admiro\Auth\Events\PinChanged;
use Simplysmart\Admiro\Auth\Events\TrustedDeviceRegistered;
use Simplysmart\Admiro\Auth\Listeners\RevokeTrustedDevicesOfAccount;
use Simplysmart\Admiro\Auth\Listeners\SendAccountLockedMail;
use Simplysmart\Admiro\Auth\Listeners\SendPinChangedMail;
use Simplysmart\Admiro\Auth\Listeners\SendTrustedDeviceMails;
use Simplysmart\Admiro\Auth\SignIn\EloquentLoginChallengeRepository;
use Simplysmart\Admiro\Auth\SignIn\LoginCodeGenerator;
use Simplysmart\Admiro\Console\PruneSignInChallenges;
use Simplysmart\Admiro\Contracts\AccountLocator;
use Simplysmart\Admiro\Contracts\AccountStore;
use Simplysmart\Admiro\Contracts\BlindIndex;
use Simplysmart\Admiro\Contracts\CurrentZone;
use Simplysmart\Admiro\Contracts\LoginChallengeRepository;
use Simplysmart\Admiro\Contracts\TrustedDeviceRepository;
use Simplysmart\Admiro\Http\SignInRoutes;
use Simplysmart\Admiro\Security\HmacBlindIndex;
use Simplysmart\Admiro\Zones\ZoneRedirects;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Logowanie stref: konta, kody, zaufane urządzenia i stan logowania w tabelach strefy, indeks e-maila oraz kody
 * i tokeny urządzeń jako HMAC z kluczem aplikacji; maile i odwołania urządzeń na zdarzeniach, limit tras
 * logowania, powrót wygasłego formularza (419) do logowania strefy i czyszczenie co godzinę.
 */
final class SignInServiceProvider extends ServiceProvider
{
    private const int PAGE_EXPIRED = 419;

    public function register(): void
    {
        $this->app->bind(AccountLocator::class, EloquentAccountLocator::class);
        $this->app->bind(AccountStore::class, EloquentAccountStore::class);
        $this->app->bind(LoginChallengeRepository::class, EloquentLoginChallengeRepository::class);
        $this->app->bind(TrustedDeviceRepository::class, EloquentTrustedDeviceRepository::class);
        $this->app->singleton(BlindIndex::class, fn(Application $app): BlindIndex => new HmacBlindIndex($this->appKey($app)));
        $this->app->singleton(LoginCodeGenerator::class, fn(Application $app): LoginCodeGenerator => new LoginCodeGenerator($this->appKey($app)));
        $this->app->singleton(DeviceTokenIssuer::class, fn(Application $app): DeviceTokenIssuer => new DeviceTokenIssuer($this->appKey($app)));
    }

    public function boot(Dispatcher $events, RateLimiter $limiter): void
    {
        $events->listen(AccountLocked::class, SendAccountLockedMail::class);
        $events->listen(PinChanged::class, SendPinChangedMail::class);
        $events->listen([AccountAccessRevoked::class, AccountLocked::class], RevokeTrustedDevicesOfAccount::class);
        $events->listen([TrustedDeviceRegistered::class, AccountAccessRevoked::class], SendTrustedDeviceMails::class);

        $limiter->for(SignInRoutes::THROTTLE, fn(Request $request): Limit => $this->signInRouteLimit($request));
        $this->callAfterResolving(ExceptionHandler::class, $this->renderExpiredFormsAsSignIn(...));

        if ($this->app->runningInConsole()) {
            $this->commands([PruneSignInChallenges::class]);
            $this->callAfterResolving(Schedule::class, static function (Schedule $schedule): void {
                $schedule->command(PruneSignInChallenges::class)->hourly();
            });
        }
    }

    private function appKey(Application $app): string
    {
        $key = $app->make(Repository::class)->get('app.key');

        return is_string($key) && $key !== '' ? $key : throw new RuntimeException('APP_KEY is required for sign-in codes and the e-mail index.');
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
}
