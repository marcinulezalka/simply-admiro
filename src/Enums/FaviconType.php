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
 * Rodzaj ikony strony strefy (`favicon` w configu strefy, Zones\ZoneFavicon): plik `.ico` dla starszych przeglądarek,
 * wektorowa ikona SVG i ikona ekranu startowego (apple-touch-icon, PNG 180×180). Kolejność przypadków to kolejność
 * znaczników `<link>` w `<head>`.
 */
enum FaviconType: string
{
    case Ico = 'ico';
    case Svg = 'svg';
    case Apple = 'apple';

    /**
     * Rozszerzenie pliku, które ten rodzaj przyjmuje.
     */
    public function extension(): string
    {
        return match ($this) {
            self::Ico => 'ico',
            self::Svg => 'svg',
            self::Apple => 'png',
        };
    }

    public function rel(): string
    {
        return $this === self::Apple ? 'apple-touch-icon' : 'icon';
    }

    /**
     * Typ MIME dla atrybutu `type` (tylko SVG: przeglądarka bez obsługi SVG weźmie wtedy `.ico`).
     */
    public function mediaType(): ?string
    {
        return $this === self::Svg ? 'image/svg+xml' : null;
    }

    /**
     * Atrybut `sizes` (`any` dla `.ico`, żeby przeglądarka wolała SVG).
     */
    public function sizes(): ?string
    {
        return $this === self::Ico ? 'any' : null;
    }
}
