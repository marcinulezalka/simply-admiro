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
use Simplysmart\Mainboard\Auth\SignIn\LoginChallengeRecord;

/**
 * Kody logowania kont bieżącej strefy (tabela <strefa>_login_challenges); kod tylko jako HMAC.
 */
interface LoginChallengeRepository
{
    /**
     * Najnowszy niezużyty kod konta.
     */
    public function latestActive(int|string $accountId): ?LoginChallengeRecord;

    public function create(int|string $accountId, string $codeHash, CarbonInterface $expiresAt, ?string $ip, ?string $userAgent): void;

    /**
     * Unieważnia wszystkie niezużyte kody konta (ważny jest tylko najnowszy).
     */
    public function consumeAll(int|string $accountId): void;

    public function recordFailedAttempt(string $challengeId): void;

    /**
     * Poprawny kod: oznacza go jako zweryfikowany i zużyty.
     */
    public function markVerified(string $challengeId): void;

    /**
     * Usuwa kody utworzone przed podanym czasem; zwraca ich liczbę.
     */
    public function pruneCreatedBefore(CarbonInterface $before): int;
}
