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
 * Rozmiar tekstu (A / AA / AAA; `data-text-size` na `<html>`, js/theme.js): cała strona skaluje się jak powiększenie
 * przeglądarki, więc tekst rośnie razem z układem i nic na siebie nie nachodzi (WCAG 1.4.4). Normal — bez atrybutu.
 */
enum TextSize: string
{
    case Normal = 'normal';
    case Large = 'large';
    case Larger = 'larger';

    /**
     * Znak na przycisku przełącznika (x-admiro::preference-toggle).
     */
    public function glyph(): string
    {
        return match ($this) {
            self::Normal => 'A',
            self::Large => 'AA',
            self::Larger => 'AAA',
        };
    }

    public function label(): string
    {
        return text('layout.text_sizes.' . $this->value);
    }
}
