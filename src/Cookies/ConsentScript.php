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

use Simplysmart\Admiro\Enums\CookieCategory;

/**
 * Skrypt opcjonalny (np. analityka) ładowany dopiero po zgodzie na kategorię: adres skryptu (zewnętrzny wymaga
 * dopisania domeny do `admiro.security.csp.policy` przez paczkę, która go rejestruje).
 */
final readonly class ConsentScript
{
    public function __construct(
        public CookieCategory $category,
        public string $src,
    ) {}
}
