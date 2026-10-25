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
 * Motyw strony wybierany w przełączniku x-admiro::theme-toggle (atrybut `data-theme` na `<html>`, js/theme.js):
 * jasny, ciemny i dwa motywy wysokiego kontrastu WCAG 2.2 (tokens.css, kontrast co najmniej 7:1). Kolejność
 * przypadków to kolejność przycisków; lista wartości w js/theme.js musi być taka sama.
 */
enum Theme: string
{
    case Light = 'light';
    case Dark = 'dark';
    case Contrast = 'contrast';
    case ContrastYellow = 'contrast-yellow';

    /**
     * Ikona przycisku (public/assets/img/icons/).
     */
    public function icon(): string
    {
        return match ($this) {
            self::Light => 'sun',
            self::Dark => 'moon',
            self::Contrast => 'contrast',
            self::ContrastYellow => 'eye',
        };
    }

    public function label(): string
    {
        return text('layout.themes.' . $this->value);
    }
}
