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

namespace Simplysmart\Admiro\Zones;

/**
 * Krój do wyboru w strefie (`font_styles` w configu strefy) obok jej kroju bazowego: `data-font` = $key na `<html>`
 * (js/theme.js), a arkusz strefy przestawia dla `:root[data-font="<key>"]` tokeny --ss-font-* i próbkę
 * `[data-ss-font-sample="<key>"]` na stronie wyglądu.
 */
final readonly class ZoneFontStyle
{
    /**
     * @param string $key Klucz kroju (małe litery, cyfry i myślniki).
     * @param string $labelKey Klucz tekstu z nazwą kroju (np. `shop::appearance.fonts.serif`).
     */
    public function __construct(
        public string $key,
        public string $labelKey,
    ) {}

    public function label(): string
    {
        return text($this->labelKey);
    }
}
