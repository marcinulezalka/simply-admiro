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

namespace Simplysmart\Mainboard\Auth\Devices;

use Carbon\CarbonImmutable;

/**
 * Zaufane urządzenie odczytane z bazy (validator tylko jako HMAC).
 */
final readonly class TrustedDeviceRecord
{
    public function __construct(
        public string $id,
        public string $token,
        public int|string $accountId,
        public string $selector,
        public string $validatorHash,
        public string $name,
        public ?string $userAgent,
        public CarbonImmutable $createdAt,
        public ?CarbonImmutable $lastUsedAt,
        public CarbonImmutable $expiresAt,
    ) {}

    public function belongsTo(int|string $accountId): bool
    {
        return (string) $this->accountId === (string) $accountId;
    }
}
