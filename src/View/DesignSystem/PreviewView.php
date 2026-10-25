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

namespace Simplysmart\Admiro\View\DesignSystem;

use Closure;

/**
 * Gotowy widok w katalogu design systemu: klucz w adresie, etykieta (klucz tekstu), szablon i leniwe dane.
 */
final readonly class PreviewView
{
    /**
     * @param (Closure(): array<string, mixed>)|null $data
     */
    public function __construct(
        public string $area,
        public string $key,
        public string $labelKey,
        public string $template,
        private ?Closure $data = null,
    ) {}

    public function label(): string
    {
        return text($this->labelKey);
    }

    /**
     * @return array<string, mixed>
     */
    public function data(): array
    {
        return $this->data === null ? [] : ($this->data)();
    }
}
