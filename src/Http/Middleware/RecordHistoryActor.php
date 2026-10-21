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

namespace Simplysmart\Mainboard\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Database\HistoryActor;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ustawia aktora historii zmian na czas żądania: strefa, konto z jej jawnie nazwanego guarda
 * i identyfikator żądania; po odpowiedzi czyści go.
 */
final readonly class RecordHistoryActor
{
    public function __construct(
        private CurrentZone $zone,
        private AuthFactory $auth,
        private HistoryActor $actor,
    ) {}

    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $zone = $this->zone->get();
        $accountId = $zone->requiresLogin() ? $this->auth->guard($zone->guard)->id() : null;

        $this->actor->set($zone->name, is_int($accountId) || is_string($accountId) ? $accountId : null, (string) Str::uuid());

        try {
            return $next($request);
        } finally {
            $this->actor->clear();
        }
    }
}
