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

namespace Simplysmart\Mainboard\Providers;

use Illuminate\Cache\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Foundation\CachesRoutes;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Foundation\Http\Kernel as FoundationKernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Simplysmart\Mainboard\Console\UnbanIp;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Http\HoneypotRoutes;
use Simplysmart\Mainboard\Http\Middleware\BlockBannedIps;
use Simplysmart\Mainboard\Security\ErrorMasker;
use Simplysmart\Mainboard\Security\TrustedProxies;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Bezpieczeństwo aplikacji: zaufane proxy, blokada destrukcyjnych komend na produkcji, maskowanie błędów (404)
 * i pułapki na boty (globalna blokada zbanowanych adresów, trasy pułapek, komenda zdejmowania bana).
 */
final class SecurityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ErrorMasker::class, fn(Application $app): ErrorMasker => new ErrorMasker(
            $app->make(Repository::class),
            $app->make(CurrentZone::class),
            $app->environment('production') === true,
        ));
    }

    public function boot(TrustedProxies $trustedProxies, RateLimiter $limiter, Repository $config): void
    {
        $trustedProxies->apply();
        DB::prohibitDestructiveCommands($this->app->environment('production') === true && ! $config->boolean('mainboard.security.allow_destructive_commands'));

        $this->callAfterResolving(ExceptionHandler::class, $this->maskErrors(...));

        $limiter->for(HoneypotRoutes::THROTTLE, static fn(Request $request): Limit => Limit::perMinute($config->integer('mainboard.honeypot.route_throttle_per_minute'))->by((string) $request->ip()));
        $this->registerHoneypot($config);

        if ($this->app->runningInConsole()) {
            $this->commands([UnbanIp::class]);
        }
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

        $handler->respondUsing(function (Response $response, Throwable $exception, Request $request) use ($handler): Response {
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
    private function registerHoneypot(Repository $config): void
    {
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
}
