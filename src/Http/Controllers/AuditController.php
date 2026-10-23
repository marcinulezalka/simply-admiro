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

namespace Simplysmart\Admiro\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Simplysmart\Admiro\Http\ManagedAccounts;
use Simplysmart\Mainboard\Queries\Audit\AuditLog;
use Throwable;

/**
 * Dziennik zdarzeń (`audit.view`): logowania, blokady, role i akcje administratorów, od najnowszych, z filtrami
 * strefy, zdarzenia i zakresu dat.
 */
final readonly class AuditController
{
    private const int PER_PAGE = 50;

    public function __invoke(Request $request, AuditLog $log, ManagedAccounts $managed): View
    {
        $filters = [
            'zone' => $this->string($request, 'zone'),
            'event' => $this->string($request, 'event'),
            'from' => $this->string($request, 'from'),
            'to' => $this->string($request, 'to'),
        ];

        return view('admiro::audit.index', [
            'entries' => $log->page($filters['zone'], $filters['event'], $this->date($filters['from']), $this->date($filters['to']), self::PER_PAGE),
            'events' => $log->events(),
            'zones' => $managed->zones(),
            'filters' => $filters,
        ]);
    }

    private function string(Request $request, string $key): ?string
    {
        $value = $request->query($key);

        return is_string($value) && $value !== '' ? $value : null;
    }

    private function date(?string $value): ?Carbon
    {
        if ($value === null) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value) ?: null;
        } catch (Throwable) {
            return null;
        }
    }
}
