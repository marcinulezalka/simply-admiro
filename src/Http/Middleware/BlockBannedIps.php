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
use Illuminate\Http\Request;
use Simplysmart\Admiro\Security\Honeypot\IpBans;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Globalny middleware (dodawany przez Core, gdy honeypot jest włączony): zbanowany adres dostaje 403
 * (z maskowaniem: 404) na każdym adresie, zanim powstanie sesja.
 */
final readonly class BlockBannedIps
{
    public function __construct(
        private IpBans $bans,
    ) {}

    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->bans->isBanned((string) $request->ip())) {
            throw new AccessDeniedHttpException();
        }

        return $next($request);
    }
}
