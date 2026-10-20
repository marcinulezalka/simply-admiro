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

namespace Simplysmart\Mainboard\Auth;

use Simplysmart\Mainboard\Contracts\SignInStep;

/**
 * Ustawienia logowania strefy: domyślne z `mainboard.auth`, nadpisane kluczem `auth` configu strefy.
 */
final readonly class ZoneAuthSettings
{
    /**
     * @param list<class-string<SignInStep>> $steps
     * @param list<int> $lockoutMinutes Czas kolejnych blokad; ostatni powtarza się dla dalszych.
     * @param array<string, array{max: int, decay_minutes: int}> $limits
     */
    public function __construct(
        public array $steps,
        public int $codeLength,
        public int $codeTtlMinutes,
        public int $codeMaxAttempts,
        public int $codeResendCooldownSeconds,
        public int $pinLength,
        public int $pinStageTtlMinutes,
        public int $pinMaxAttempts,
        public array $lockoutMinutes,
        public array $limits,
        public int $routeThrottlePerMinute,
        public int $challengePruneHours,
    ) {}

    /**
     * Czas blokady po `$lockLevel` wcześniejszych blokadach.
     */
    public function lockoutMinutesFor(int $lockLevel): int
    {
        return $this->lockoutMinutes[min(max($lockLevel, 0), count($this->lockoutMinutes) - 1)];
    }

    /**
     * @return array{max: int, decay_minutes: int}
     */
    public function limit(SignInLimit $limit): array
    {
        return $this->limits[$limit->value];
    }
}
