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

use Simplysmart\Admiro\Enums\LayoutWidth;
use Simplysmart\Admiro\Enums\TextSize;
use Simplysmart\Admiro\Zones\ZoneDefinition;
use Simplysmart\Admiro\Zones\ZoneFontStyle;

/**
 * Opcje przełączników wyglądu na stronie wyglądu konta (Enums\AppearancePreference): rozmiar tekstu, szerokość treści
 * i kroje strefy (krój bazowy strefy i jej `font_styles`).
 */
final readonly class AppearanceChoices
{
    /** Próbka kroju na przyciskach wyboru kroju. */
    private const string FONT_GLYPH = 'Aa';

    /**
     * @return list<PreferenceOption>
     */
    public function textSizes(): array
    {
        return array_map(static fn(TextSize $size): PreferenceOption => new PreferenceOption($size->value, $size->label(), $size->glyph()), TextSize::cases());
    }

    /**
     * @return list<PreferenceOption>
     */
    public function layoutWidths(): array
    {
        return array_map(static fn(LayoutWidth $width): PreferenceOption => new PreferenceOption($width->value, $width->label(), icon: $width->icon()), LayoutWidth::cases());
    }

    /**
     * Krój bazowy strefy (pusta wartość) i jej kroje; pusta lista, gdy strefa nie ma krojów do wyboru.
     *
     * @return list<PreferenceOption>
     */
    public function fontStyles(ZoneDefinition $zone): array
    {
        if ($zone->fontStyles === []) {
            return [];
        }

        return [
            new PreferenceOption('', text('layout.font_styles.default'), self::FONT_GLYPH),
            ...array_map(static fn(ZoneFontStyle $style): PreferenceOption => new PreferenceOption($style->key, $style->label(), self::FONT_GLYPH, sample: $style->key), $zone->fontStyles),
        ];
    }
}
