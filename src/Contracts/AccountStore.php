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

namespace Simplysmart\Mainboard\Contracts;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Jedyne miejsce zapisu stanu logowania konta (PIN, blokady, epoka, ostatnie logowanie).
 */
interface AccountStore
{
    /**
     * Zapisuje błędny PIN; zwraca liczbę błędnych prób od ostatniego udanego logowania.
     */
    public function recordFailedPin(Model&CodeAndPinAccount $account): int;

    /**
     * Blokuje konto do podanego czasu i podnosi poziom blokady.
     */
    public function lock(Model&CodeAndPinAccount $account, CarbonInterface $until): void;

    public function setPinHash(Model&CodeAndPinAccount $account, string $hash): void;

    /**
     * Udane logowanie: zeruje błędy i poziom blokady, zapisuje czas i IP.
     */
    public function recordSignIn(Model&CodeAndPinAccount $account, ?string $ip): void;

    /**
     * Unieważnia wszystkie sesje konta (każda sesja z poprzednią epoką zostanie wylogowana).
     */
    public function bumpAuthEpoch(Model&CodeAndPinAccount $account): void;
}
