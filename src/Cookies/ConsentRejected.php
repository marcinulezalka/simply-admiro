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

namespace Simplysmart\Admiro\Cookies;

use RuntimeException;

/**
 * Zgoda na cookies odrzucona: identyfikator zgody nie jest UUID albo wersja polityki cookies nie jest bieżąca.
 */
final class ConsentRejected extends RuntimeException
{
    public static function because(string $reason): self
    {
        return new self("Cookie consent rejected: {$reason}");
    }
}
