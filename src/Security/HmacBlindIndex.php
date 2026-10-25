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

namespace Simplysmart\Mainboard\Security;

use Simplysmart\Mainboard\Contracts\BlindIndex;

/**
 * HMAC-SHA256 z kluczem aplikacji: ten sam adres daje ten sam skrót, a bez klucza nie da się go odwrócić.
 */
final readonly class HmacBlindIndex implements BlindIndex
{
    public function __construct(
        #[\SensitiveParameter]
        private string $key,
    ) {}

    public function hash(string $value): string
    {
        return hash_hmac('sha256', $value, $this->key);
    }
}
