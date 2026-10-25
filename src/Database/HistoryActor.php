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

namespace Simplysmart\Admiro\Database;

use Illuminate\Database\DatabaseManager;

/**
 * Aktor zmian dla triggerów historii: zmienne sesji MySQL `@actor_zone`, `@actor_id`, `@request_id`.
 * Na innych silnikach nic nie robi (trigger zapisuje wtedy NULL, czyli „system”).
 */
final readonly class HistoryActor
{
    private const array DRIVERS = ['mysql', 'mariadb'];

    public function __construct(
        private DatabaseManager $connections,
    ) {}

    public function set(?string $zone, int|string|null $accountId, ?string $requestId): void
    {
        $connection = $this->connections->connection();

        if (in_array($connection->getDriverName(), self::DRIVERS, true)) {
            $connection->statement('SET @actor_zone = ?, @actor_id = ?, @request_id = ?', [$zone, $accountId, $requestId]);
        }
    }

    public function clear(): void
    {
        $this->set(null, null, null);
    }
}
