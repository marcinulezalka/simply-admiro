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

use Simplysmart\Mainboard\Contracts\CookieCatalog;

/**
 * Katalog cookies w pamięci procesu, wypełniany przez providery przy starcie (wpis o tej samej nazwie zastępuje
 * poprzedni).
 */
final class InMemoryCookieCatalog implements CookieCatalog
{
    /**
     * @var array<string, CookieDescription>
     */
    private array $cookies = [];

    public function add(CookieDescription $cookie): void
    {
        $this->cookies[$cookie->name] = $cookie;
    }

    public function all(): array
    {
        return array_values($this->cookies);
    }
}
