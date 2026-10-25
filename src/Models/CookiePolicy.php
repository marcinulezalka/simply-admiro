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

namespace Simplysmart\Mainboard\Models;

use Illuminate\Support\Carbon;
use Simplysmart\Mainboard\Database\ColumnNames;

/**
 * Wersja polityki cookies: szkic (`version` i `published_at` null) albo opublikowana wersja o kolejnym numerze.
 * Tytuł i treść (Markdown) w każdym języku interfejsu w `json_i18n` jako `{język: {title, body}}` (nie krótkie
 * nazwy jak w słownikach, więc bez `HasI18n`); `name` to etykieta
 * wersji. Nowa wersja wymaga ponownej zgody odwiedzających.
 *
 * @property int|null $version
 * @property Carbon|null $published_at
 */
final class CookiePolicy extends DataModel
{
    public const string TABLE = 'cookie_policies';

    protected $table = self::TABLE;

    /**
     * Tytuł i treść w języku `$locale` (puste, gdy brak tłumaczenia).
     *
     * @return array{title: string, body: string}
     */
    public function content(string $locale): array
    {
        $i18n = $this->getAttribute(ColumnNames::JSON_I18N);
        $entry = is_array($i18n) && is_array($i18n[$locale] ?? null) ? $i18n[$locale] : [];

        return [
            'title' => is_string($entry['title'] ?? null) ? $entry['title'] : '',
            'body' => is_string($entry['body'] ?? null) ? $entry['body'] : '',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['version' => 'integer', 'published_at' => 'datetime', ColumnNames::JSON_I18N => 'array'];
    }
}
