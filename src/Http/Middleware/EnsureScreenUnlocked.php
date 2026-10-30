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

namespace Simplysmart\Admiro\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Simplysmart\Admiro\Auth\ScreenLock;
use Simplysmart\Admiro\Contracts\CurrentZone;
use Simplysmart\Admiro\Http\AccountRoutes;
use Symfony\Component\HttpFoundation\Response;

/**
 * Zablokowany ekran (Auth\ScreenLock): każda strona dla zalogowanych prowadzi na ekran PIN-u, poza samym ekranem
 * odblokowania i wylogowaniem; adres strony GET jest zapamiętany do powrotu po odblokowaniu.
 */
final readonly class EnsureScreenUnlocked
{
    /** Trasy dostępne przy zablokowanym ekranie (krótkie nazwy). */
    private const array ALLOWED = [AccountRoutes::UNLOCK, AccountRoutes::UNLOCK_STORE, 'auth.logout'];

    public function __construct(
        private ScreenLock $lock,
        private CurrentZone $zone,
    ) {}

    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->hasSession() || ! $this->lock->isLocked($request->session())) {
            return $next($request);
        }

        $zone = $this->zone->get();
        $name = $request->route()->getName();

        if ($name !== null && in_array($name, array_map($zone->routeName(...), self::ALLOWED), true)) {
            return $next($request);
        }

        if ($request->isMethod('GET')) {
            $this->lock->lock($request->session(), $request->fullUrl());
        }

        return new RedirectResponse(route($zone->routeName(AccountRoutes::UNLOCK)));
    }
}
