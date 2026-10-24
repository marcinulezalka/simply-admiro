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

use Simplysmart\Mainboard\Contracts\HasLabel;

/**
 * Kategorie plików cookies i podobnych technologii w zgodzie odwiedzającego: niezbędne (zawsze włączone, bez zgody),
 * preferencje, analityczne i marketingowe (domyślnie wyłączone).
 */
enum CookieCategory: string implements HasLabel
{
    case Necessary = 'necessary';
    case Preferences = 'preferences';
    case Analytics = 'analytics';
    case Marketing = 'marketing';

    /**
     * Kategorie wymagające zgody (bez niezbędnych).
     *
     * @return list<self>
     */
    public static function optional(): array
    {
        return array_values(array_filter(self::cases(), static fn(self $category): bool => $category !== self::Necessary));
    }

    public function label(): string
    {
        return text('cookies.categories.' . $this->value . '.label');
    }

    public function description(): string
    {
        return text('cookies.categories.' . $this->value . '.description');
    }
}
