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
 * Zmiana polityki cookies odrzucona: szkic bez tytułu albo treści w języku domyślnym, brak szkicu do publikacji
 * albo zgoda z nieznaną wersją polityki lub kategorią.
 */
final class CookiePolicyRejected extends RuntimeException
{
    public static function because(string $reason): self
    {
        return new self("Cookie policy change rejected: {$reason}");
    }
}
