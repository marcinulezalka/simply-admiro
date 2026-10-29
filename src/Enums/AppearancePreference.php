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
 * Wybory wyglądu zapamiętywane w przeglądarce obok motywu i kolorystyki (js/theme.js, x-admiro::preference-toggle):
 * rozmiar tekstu (Enums\TextSize), szerokość treści (Enums\LayoutWidth) i krój strefy (`font_styles`,
 * Zones\ZoneFontStyle). Wartość to nazwa wyboru w skrypcie i atrybut `data-<wartość>` na `<html>`; listy wartości
 * w js/theme.js muszą być takie same jak przypadki enumów.
 */
enum AppearancePreference: string
{
    case TextSize = 'text-size';
    case LayoutWidth = 'layout-width';
    case FontStyle = 'font';

    public function label(): string
    {
        return text('layout.preferences.' . $this->value);
    }
}
