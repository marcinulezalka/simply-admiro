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

namespace Simplysmart\Admiro\Localization;

use RuntimeException;

/**
 * Zmiana ustawień lokalizacji odrzucona: brak języka interfejsu, język spoza `APP_LOCALES` albo język domyślny
 * spoza języków interfejsu.
 */
final class LocalizationSettingsRejected extends RuntimeException
{
    public static function because(string $reason): self
    {
        return new self("Localization settings rejected: {$reason}");
    }
}
