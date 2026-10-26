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

namespace Simplysmart\Admiro\View\Navigation;

/**
 * Zakres globalnej wyszukiwarki do zaznaczenia przy polu (np. języki, strony panelu): wartość pola, etykieta i stan.
 */
final readonly class SearchScope
{
    public function __construct(
        public string $value,
        public string $label,
        public bool $checked = true,
    ) {}
}
