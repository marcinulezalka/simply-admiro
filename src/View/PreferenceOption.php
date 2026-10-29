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
 * Jedna opcja przełącznika wyglądu (x-admiro::preference-toggle): wartość w js/theme.js, etykieta (title i czytniki
 * ekranu) oraz zawartość przycisku — ikona albo znak (np. „AA”, próbka kroju „Aa” z $sample).
 */
final readonly class PreferenceOption
{
    /**
     * @param string|null $icon Nazwa ikony z `public/assets/img/icons/` (x-admiro::icon); null = znak $glyph.
     * @param string|null $sample Klucz kroju strefy (`data-ss-font-sample`): znak w tym kroju; null = krój strony.
     */
    public function __construct(
        public string $value,
        public string $label,
        public string $glyph = '',
        public ?string $icon = null,
        public ?string $sample = null,
    ) {}
}
