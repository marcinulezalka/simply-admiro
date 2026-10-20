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

/**
 * Konto blokowane po błędnych PIN-ach, z rosnącym czasem blokady.
 */
interface LockableAccount
{
    public function isLocked(): bool;

    /**
     * Błędne PIN-y od ostatniego udanego logowania (nie zerują się po upływie blokady).
     */
    public function failedPinAttempts(): int;

    /**
     * Liczba blokad od ostatniego udanego logowania (wybiera czas kolejnej blokady).
     */
    public function lockLevel(): int;
}
