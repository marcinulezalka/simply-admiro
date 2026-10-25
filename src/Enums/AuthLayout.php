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

namespace Simplysmart\Mainboard\Enums;

/**
 * Układ stron logowania strefy (`auth.layout` w ustawieniach logowania, x-mainboard::auth.layout).
 */
enum AuthLayout: string
{
    /** Jedna karta na środku ekranu: logo, nagłówek i formularz. */
    case Centered = 'centered';

    /** Dwie karty obok siebie: po lewej marka (logo), po prawej nagłówek i formularz; na telefonie jedna pod drugą. */
    case Split = 'split';

    /** Dwie karty jak w Split, w odbiciu lustrzanym: po lewej nagłówek i formularz, po prawej marka; na telefonie marka nad formularzem. */
    case SplitReversed = 'split-reversed';

    /**
     * Czy układ składa się z dwóch kart (marka i formularz) w ramie.
     */
    public function isSplit(): bool
    {
        return $this !== self::Centered;
    }
}
