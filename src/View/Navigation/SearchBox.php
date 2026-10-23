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

namespace Simplysmart\Mainboard\View\Navigation;

/**
 * Globalna wyszukiwarka w top barze aplikacji: formularz GET na adres strefy, pole `$name`.
 */
final readonly class SearchBox
{
    public function __construct(
        public string $action,
        public string $placeholder,
        public string $name = 'q',
        public string $value = '',
    ) {}
}
