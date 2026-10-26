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

namespace Simplysmart\Admiro\Auth;

use Simplysmart\Admiro\Contracts\SignInStep;
use Simplysmart\Admiro\Enums\AuthLayout;

/**
 * Ustawienia logowania strefy: domyślne z `admiro.auth`, nadpisane kluczem `auth` configu strefy.
 */
final readonly class ZoneAuthSettings
{
    /**
     * @param list<class-string<SignInStep>> $steps
     * @param list<int> $lockoutMinutes Czas kolejnych blokad; ostatni powtarza się dla dalszych.
     * @param string|null $logo Ścieżka logo strefy (SVG z fill="currentColor") w karcie marki układów dwóch kart; null — logo simplysmart.
     * @param bool $homeLink Przycisk „Strona główna” pod formularzem logowania (`auth.home_link`); false — bez niego.
     * @param bool $cookiePolicyFirst Polityka cookies przed dokumentami strefy w odnośnikach prawnych karty logowania
     *                                (`auth.legal.cookie_policy_first`); false — na końcu.
     * @param bool $cookieSettingsLink Odnośnik „Ustawienia cookies” w karcie logowania (`auth.legal.cookie_settings`).
     * @param array<string, array{max: int, decay_minutes: int}> $limits
     */
    public function __construct(
        public array $steps,
        public int $codeLength,
        public int $codeTtlMinutes,
        public int $codeMaxAttempts,
        public int $codeResendCooldownSeconds,
        public PinLength $pinLength,
        public int $pinStageTtlMinutes,
        public int $pinMaxAttempts,
        public array $lockoutMinutes,
        public array $limits,
        public int $routeThrottlePerMinute,
        public int $challengePruneHours,
        public bool $trustedDevicesEnabled = false,
        public int $trustedDeviceLifetimeDays = 30,
        public int $trustedDevicesPerAccount = 10,
        public AuthLayout $layout = AuthLayout::Centered,
        public ?AuthBrand $brand = null,
        public ?string $logo = null,
        public bool $homeLink = true,
        public bool $cookiePolicyFirst = false,
        public bool $cookieSettingsLink = true,
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
