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

use Simplysmart\Admiro\Zones\ZoneDefinition;
use Simplysmart\Admiro\Zones\ZoneFontStyle;

/**
 * Jedno ustawienie wyglądu zapisywane w koncie (Profile\AccountAppearance, `appearance` w configu strefy): motyw
 * (Enums\Theme), kolorystyka (`palettes` strefy), rozmiar tekstu (Enums\TextSize), szerokość treści
 * (Enums\LayoutWidth) i krój (`font_styles` strefy). Wartość to nazwa pola zapisu i klucz w `json_attributes`
 * konta; js/theme.js wysyła te same nazwy. Wartość bazowa (base()) to wygląd bez atrybutu `data-*` na `<html>`.
 */
enum AppearanceSetting: string
{
    case Theme = 'theme';
    case Palette = 'palette';
    case TextSize = 'text-size';
    case LayoutWidth = 'layout-width';
    case Font = 'font';

    /**
     * Atrybut na `<html>`, który rysuje wybór (CSS Core i strefy).
     */
    public function attribute(): string
    {
        return 'data-' . $this->value;
    }

    /**
     * Wartość bez atrybutu: motyw systemu, kolory bazowe strefy, normalny tekst, pełna szerokość, krój bazowy.
     */
    public function base(): string
    {
        return match ($this) {
            self::TextSize => TextSize::Normal->value,
            self::LayoutWidth => LayoutWidth::Full->value,
            default => '',
        };
    }

    /**
     * Wartości do wyboru w strefie (z wartością bazową).
     *
     * @return list<string>
     */
    public function values(ZoneDefinition $zone): array
    {
        return $this->choices($zone->palettes, $zone->fontStyles);
    }

    /**
     * Wartości do wyboru przy danych kolorystykach i krojach strefy (także przed zbudowaniem jej definicji).
     *
     * @param list<Palette> $palettes
     * @param list<ZoneFontStyle> $fontStyles
     *
     * @return list<string>
     */
    public function choices(array $palettes, array $fontStyles): array
    {
        return match ($this) {
            self::Theme => ['', ...array_map(static fn(Theme $theme): string => $theme->value, Theme::cases())],
            self::Palette => ['', ...array_map(static fn(Palette $palette): string => $palette->value, $palettes)],
            self::TextSize => array_map(static fn(TextSize $size): string => $size->value, TextSize::cases()),
            self::LayoutWidth => array_map(static fn(LayoutWidth $width): string => $width->value, LayoutWidth::cases()),
            self::Font => ['', ...array_map(static fn(ZoneFontStyle $style): string => $style->key, $fontStyles)],
        };
    }
}
