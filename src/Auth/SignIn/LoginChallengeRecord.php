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

namespace Simplysmart\Mainboard\Auth\SignIn;

use Carbon\CarbonImmutable;

/**
 * Kod logowania odczytany z bazy (bez wartości kodu, tylko jej HMAC).
 */
final readonly class LoginChallengeRecord
{
    public function __construct(
        public string $id,
        public string $codeHash,
        public int $attempts,
        public CarbonImmutable $expiresAt,
        public CarbonImmutable $createdAt,
    ) {}

    public function isUsable(int $maxAttempts): bool
    {
        return $this->attempts < $maxAttempts && $this->expiresAt->isFuture();
    }
}
