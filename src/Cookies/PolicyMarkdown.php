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

namespace Simplysmart\Mainboard\Cookies;

use Illuminate\Support\Str;

/**
 * Treść polityki z Markdown do HTML-a strony: surowy HTML z treści jest wycinany, a niebezpieczne odnośniki
 * (`javascript:` i podobne) blokowane, więc administrator nie wstrzyknie skryptu.
 */
final class PolicyMarkdown
{
    public static function html(string $markdown): string
    {
        return Str::markdown($markdown, ['html_input' => 'strip', 'allow_unsafe_links' => false]);
    }
}
