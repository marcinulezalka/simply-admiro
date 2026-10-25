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

/**
 * Przyjazna nazwa urządzenia z user agenta („Firefox / Linux”). Nie decyduje o zaufaniu (to robi token),
 * ale zmiana przeglądarki albo systemu względem zapamiętanego urządzenia kończy jego zaufanie.
 */
final class DeviceNameResolver
{
    private const string UNKNOWN = '?';

    /**
     * Kolejność ma znaczenie: Edge i Opera podają też „Chrome”, a Chrome także „Safari”.
     *
     * @var array<string, string>
     */
    private const array BROWSERS = [
        'Edg/' => 'Edge',
        'OPR/' => 'Opera',
        'Firefox/' => 'Firefox',
        'Chrome/' => 'Chrome',
        'Safari/' => 'Safari',
    ];

    /**
     * @var array<string, string>
     */
    private const array SYSTEMS = [
        'Android' => 'Android',
        'iPhone' => 'iOS',
        'iPad' => 'iPadOS',
        'Windows' => 'Windows',
        'Mac OS X' => 'macOS',
        'CrOS' => 'ChromeOS',
        'Linux' => 'Linux',
    ];

    public function name(?string $userAgent): string
    {
        return $this->match($userAgent, self::BROWSERS) . ' / ' . $this->match($userAgent, self::SYSTEMS);
    }

    /**
     * Czy oba user agenty to ta sama przeglądarka w tym samym systemie (wersje się nie liczą).
     */
    public function sameDevice(?string $remembered, ?string $current): bool
    {
        return $this->name($remembered) === $this->name($current);
    }

    /**
     * @param array<string, string> $families
     */
    private function match(?string $userAgent, array $families): string
    {
        foreach ($families as $needle => $family) {
            if ($userAgent !== null && str_contains($userAgent, $needle)) {
                return $family;
            }
        }

        return self::UNKNOWN;
    }
}
