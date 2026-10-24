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

namespace Simplysmart\Mainboard\Cookies;

use Simplysmart\Mainboard\Enums\CookieCategory;

/**
 * Opis pliku cookie (albo wpisu w pamięci przeglądarki) w katalogu: nazwa (może mieć wzorzec, np. `*_session`),
 * kategoria, klucz tekstu celu i klucz tekstu czasu przechowywania (`text()`).
 */
final readonly class CookieDescription
{
    public function __construct(
        public string $name,
        public CookieCategory $category,
        public string $purposeKey,
        public string $lifetimeKey,
    ) {}

    public function purpose(): string
    {
        return text($this->purposeKey);
    }

    public function lifetime(): string
    {
        return text($this->lifetimeKey);
    }
}
