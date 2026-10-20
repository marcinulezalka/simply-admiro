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

/**
 * Konto z PIN-em (drugi czynnik logowania).
 */
interface HasPinCredential
{
    /**
     * Hash PIN-u; null, gdy PIN nie został jeszcze ustawiony.
     */
    public function pinHash(): ?string;

    public function hasPin(): bool;
}
