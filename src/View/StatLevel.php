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

namespace Simplysmart\Admiro\View;

/**
 * Poziom wypełnienia miernika i słupków karty statystyk (x-admiro::stat-card) w procentach zaokrąglonych do STEP.
 * Widok podaje go w atrybucie `data-ss-level`, a CSS zamienia go na szerokość albo wysokość (`--ss-level`) — bez
 * stylów inline, których CSP nie dopuszcza.
 */
final class StatLevel
{
    public const int STEP = 5;

    /**
     * Udział wartości w maksimum (0–100, co STEP); maksimum nie większe od zera daje 0, wartość niezerowa — co najmniej
     * jeden krok, żeby była widoczna.
     */
    public static function of(int|float $value, int|float $max): int
    {
        if ($max <= 0 || $value <= 0) {
            return 0;
        }

        $level = (int) (round(min(1, $value / $max) * 100 / self::STEP) * self::STEP);

        return max(self::STEP, $level);
    }
}
