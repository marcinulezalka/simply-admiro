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
 * Pozycja menu wykonywana formularzem POST (np. przełączenie organizacji): adres, pole i wartość, z tokenem CSRF.
 */
final readonly class NavigationForm
{
    public function __construct(
        public string $label,
        public string $action,
        public string $field,
        public string $value,
        public string $icon,
        public bool $current = false,
    ) {}
}
