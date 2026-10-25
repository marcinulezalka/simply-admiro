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

use Illuminate\Support\HtmlString;

/**
 * Wyróżnia nazwy produktów simplysmart w tekście interfejsu: tekst jest najpierw escapowany, potem same
 * nazwy dostają `<strong class="ss-brand-name">`, więc tłumaczenia zostają zwykłym tekstem bez HTML.
 */
final class BrandNames
{
    /**
     * Nazwy wyróżniane jako całe słowa, z rozróżnieniem wielkości liter.
     *
     * @var list<string>
     */
    public const array NAMES = ['simplysmart', 'admiro'];

    /**
     * Logo simplysmart osadzane w tekście zamiast nazwy (np. w nagłówku strony startowej).
     */
    public const string LOGO_NAME = 'simplysmart';

    public static function highlight(string $text): HtmlString
    {
        $pattern = '/\b(' . implode('|', array_map(preg_quote(...), self::NAMES)) . ')\b/u';

        return new HtmlString((string) preg_replace($pattern, '<strong class="ss-brand-name">$1</strong>', e($text)));
    }

    /**
     * Jak highlight(), ale nazwa simplysmart (całe słowo) staje się logo SVG w linii tekstu (`.ss-logo-inline`,
     * fill="currentColor", aria-label zachowuje nazwę dla czytników ekranu); pozostałe nazwy są wyróżnione.
     */
    public static function withLogo(string $text): HtmlString
    {
        $parts = preg_split('/\b' . preg_quote(self::LOGO_NAME, '/') . '\b/u', $text) ?: [$text];
        $logo = '<span class="ss-logo-inline">' . trim(CoreAssets::contents('img/brand/logo.svg')) . '</span>';

        return new HtmlString(implode($logo, array_map(static fn(string $part): string => self::highlight($part)->toHtml(), $parts)));
    }
}
