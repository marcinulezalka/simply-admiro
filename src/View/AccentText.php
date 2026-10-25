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

namespace Simplysmart\Mainboard\View;

use Illuminate\Support\HtmlString;

/**
 * Tekst z wyróżnioną częścią: fragment w gwiazdkach (`Nowoczesna opieka *zaczyna się* od…`) dostaje
 * `<em class="ss-accent-text">`, reszta jest escapowana; `plain()` zwraca tekst bez gwiazdek (np. do <title>).
 */
final class AccentText
{
    private const string PATTERN = '/\*([^*]+)\*/';

    public static function html(string $text): HtmlString
    {
        $escaped = e($text);

        return new HtmlString((string) preg_replace(self::PATTERN, '<em class="ss-accent-text">$1</em>', $escaped));
    }

    public static function plain(string $text): string
    {
        return (string) preg_replace(self::PATTERN, '$1', $text);
    }
}
