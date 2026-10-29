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

namespace Simplysmart\Admiro\Enums;

/**
 * Szerokość treści layoutu aplikacji (`data-layout-width` na `<html>`, js/theme.js): Full — treść na całą szerokość
 * okna (bez atrybutu), Centered — kolumna treści wyśrodkowana, najwyżej --ss-centered-width.
 */
enum LayoutWidth: string
{
    case Full = 'full';
    case Centered = 'centered';

    /**
     * Ikona przycisku przełącznika (public/assets/img/icons/).
     */
    public function icon(): string
    {
        return match ($this) {
            self::Full => 'sidebar',
            self::Centered => 'list',
        };
    }

    public function label(): string
    {
        return text('layout.layout_widths.' . $this->value);
    }
}
