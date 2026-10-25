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

namespace Simplysmart\Admiro\View;

/**
 * Jeden token design systemu z `tokens.css`: nazwa i wartość w motywie jasnym oraz ciemnym (po rozwiązaniu `var()`).
 */
final readonly class DesignToken
{
    /**
     * @param string $name Pełna nazwa właściwości, np. `--ss-color-surface`.
     * @param string $key Nazwa bez prefiksu grupy, np. `surface`.
     */
    public function __construct(
        public string $name,
        public string $key,
        public string $light,
        public string $dark,
    ) {}

    public function isThemed(): bool
    {
        return $this->light !== $this->dark;
    }
}
