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

namespace Simplysmart\Mainboard\Enums;

/**
 * Kolorystyka nakładana na motyw (tokeny `--ss-palette-<paleta>-*` w tokens.css, atrybut `data-palette` na `<html>`):
 * podmienia akcent, informację i kartę odwróconą, w jasnym i ciemnym motywie (kontrast co najmniej 4,5:1). Strefa
 * wybiera oferowane kolorystyki w `palettes` swojego configu; bez wyboru obowiązują kolory bazowe strefy.
 */
enum Palette: string
{
    case Teal = 'teal';
    case Indigo = 'indigo';
    case Blue = 'blue';
    case Emerald = 'emerald';
    case Violet = 'violet';

    public function label(): string
    {
        return text('layout.palettes.' . $this->value);
    }
}
