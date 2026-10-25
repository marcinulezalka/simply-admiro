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

    public static function highlight(string $text): HtmlString
    {
        $pattern = '/\b(' . implode('|', array_map(preg_quote(...), self::NAMES)) . ')\b/u';

        return new HtmlString((string) preg_replace($pattern, '<strong class="ss-brand-name">$1</strong>', e($text)));
    }
}
