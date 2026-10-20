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

namespace Simplysmart\Mainboard\Auth\SignIn;

/**
 * Jednorazowe kody cyfrowe: losowanie z `random_int`, zapis wyłącznie jako HMAC-SHA256 z kluczem
 * aplikacji, porównanie w stałym czasie.
 */
final readonly class LoginCodeGenerator
{
    public function __construct(
        #[\SensitiveParameter]
        private string $key,
    ) {}

    public function generate(int $length): string
    {
        return str_pad((string) random_int(0, 10 ** $length - 1), $length, '0', STR_PAD_LEFT);
    }

    public function hash(string $code): string
    {
        return hash_hmac('sha256', $code, $this->key);
    }

    public function matches(string $code, string $hash): bool
    {
        return hash_equals($hash, $this->hash($code));
    }
}
