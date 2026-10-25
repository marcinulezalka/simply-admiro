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

namespace Simplysmart\Admiro\Contracts;

use Carbon\CarbonInterface;
use Simplysmart\Admiro\Auth\Devices\TrustedDeviceRecord;

/**
 * Zaufane urządzenia kont bieżącej strefy (`<strefa>_trusted_devices`). Aktywne urządzenie to
 * nieodwołane i niewygasłe.
 */
interface TrustedDeviceRepository
{
    /**
     * Aktywne urządzenie o tym selektorze (z ciasteczka).
     */
    public function findActiveBySelector(string $selector): ?TrustedDeviceRecord;

    /**
     * Aktywne urządzenie konta o tym tokenie (z URL panelu); urządzenie innego konta to null.
     */
    public function findActiveByToken(int|string $accountId, string $token): ?TrustedDeviceRecord;

    /**
     * Aktywne urządzenia konta, od najnowszego.
     *
     * @return list<TrustedDeviceRecord>
     */
    public function activeFor(int|string $accountId): array;

    public function create(
        int|string $accountId,
        string $selector,
        string $validatorHash,
        string $name,
        ?string $ip,
        ?string $userAgent,
        CarbonInterface $expiresAt,
    ): TrustedDeviceRecord;

    /**
     * Nowy validator po użyciu urządzenia (zapisuje też czas użycia); termin ważności się nie zmienia.
     */
    public function rotate(string $id, string $validatorHash): void;

    public function revoke(string $id): void;

    /**
     * Odwołuje wszystkie aktywne urządzenia konta; zwraca ich liczbę.
     */
    public function revokeAll(int|string $accountId): int;

    /**
     * Usuwa urządzenia odwołane lub wygasłe przed podanym czasem.
     */
    public function pruneInactiveBefore(CarbonInterface $before): int;
}
