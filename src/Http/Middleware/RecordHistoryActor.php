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

use Simplysmart\Admiro\Contracts\CurrentAccount;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Simplysmart\Admiro\Contracts\CurrentZone;
use Simplysmart\Admiro\Database\HistoryActor;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ustawia aktora historii zmian na czas żądania: strefa, konto z jej jawnie nazwanego guarda
 * i identyfikator żądania; po odpowiedzi czyści go.
 */
final readonly class RecordHistoryActor
{
    public function __construct(
        private CurrentZone $zone,
        private CurrentAccount $account,
        private HistoryActor $actor,
    ) {}

    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $zone = $this->zone->get();

        $this->actor->set($zone->name, $this->account->id(), (string) Str::uuid());

        try {
            return $next($request);
        } finally {
            $this->actor->clear();
        }
    }
}
