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

/**
 * Pliki marki strefy (config `brand`) i organizacji (panel): logo, sygnet i ikona strony. Logo i sygnet trafiają
 * też do e-maili, więc tylko formaty, które klienci poczty wyświetlają (bez SVG).
 */
enum BrandAsset: string
{
    case Logo = 'logo';
    case Signet = 'signet';
    case Favicon = 'favicon';

    /**
     * Dozwolone rozszerzenia (małe litery).
     *
     * @return list<string>
     */
    public function extensions(): array
    {
        return match ($this) {
            self::Logo, self::Signet => ['png', 'jpg', 'jpeg'],
            self::Favicon => ['png', 'ico'],
        };
    }

    /**
     * Największy rozmiar pliku w KB (reguła walidacji `max`).
     */
    public function maxKilobytes(): int
    {
        return match ($this) {
            self::Logo, self::Signet => 1024,
            self::Favicon => 256,
        };
    }

    /**
     * Typ MIME pliku o danym rozszerzeniu.
     */
    public static function mimeType(string $extension): string
    {
        return match (strtolower($extension)) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'ico' => 'image/vnd.microsoft.icon',
            default => 'application/octet-stream',
        };
    }

    public function accepts(string $extension): bool
    {
        return in_array(strtolower($extension), $this->extensions(), true);
    }
}
