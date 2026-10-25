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

namespace Simplysmart\Admiro\Cookies;

use Illuminate\Support\Str;

/**
 * Treść polityki z Markdown do HTML-a strony: surowy HTML z treści jest wycinany, a niebezpieczne odnośniki
 * (`javascript:` i podobne) blokowane, więc administrator nie wstrzyknie skryptu. `document()` dodatkowo nadaje
 * nagłówkom sekcji (`##`) identyfikatory i zwraca z nich spis treści.
 */
final class PolicyMarkdown
{
    private const string SECTION_PATTERN = '~<h2>(.*?)</h2>~s';

    public static function html(string $markdown): string
    {
        return Str::markdown($markdown, ['html_input' => 'strip', 'allow_unsafe_links' => false]);
    }

    /**
     * HTML treści z identyfikatorami nagłówków `##` (z ich tekstu, unikalne; bez liter i cyfr — `sekcja-<n>`)
     * i spis treści w kolejności nagłówków.
     *
     * @return array{html: string, toc: list<array{id: string, label: string}>}
     */
    public static function document(string $markdown): array
    {
        $toc = [];
        $html = (string) preg_replace_callback(self::SECTION_PATTERN, static function (array $match) use (&$toc): string {
            $label = trim(html_entity_decode(strip_tags($match[1]), ENT_QUOTES | ENT_HTML5));
            $base = Str::slug($label);
            $base = $base !== '' ? $base : 'sekcja-' . (count($toc) + 1);
            $ids = array_column($toc, 'id');
            $id = $base;

            for ($suffix = 2; in_array($id, $ids, true); $suffix++) {
                $id = $base . '-' . $suffix;
            }

            $toc[] = ['id' => $id, 'label' => $label];

            return '<h2 id="' . $id . '">' . $match[1] . '</h2>';
        }, self::html($markdown));

        return ['html' => $html, 'toc' => $toc];
    }
}
