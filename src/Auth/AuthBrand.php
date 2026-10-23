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

namespace Simplysmart\Mainboard\Auth;

use InvalidArgumentException;

/**
 * Baner logowania strefy (`auth.brand` w configu strefy): obraz w karcie marki układu dwóch kart zamiast logo, osobno
 * dla motywu jasnego i ciemnego, z tekstem alternatywnym. Pliki serwuje trasa `<strefa>.auth.brand`.
 */
final readonly class AuthBrand
{
    public const string LIGHT = 'light';

    public const string DARK = 'dark';

    /**
     * Rozszerzenia obrazów, które trasa banera serwuje.
     *
     * @var list<string>
     */
    public const array EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'avif', 'svg'];

    /**
     * @param string $light Ścieżka pliku obrazu (motyw jasny i domyślny).
     * @param string|null $dark Ścieżka pliku obrazu dla motywu ciemnego.
     * @param string $alt Klucz tekstu alternatywnego dla `text()`; pusty — baner dekoracyjny.
     *
     * @throws InvalidArgumentException Gdy pliku nie ma albo nie jest obsługiwanym obrazem.
     */
    public function __construct(
        public string $light,
        public ?string $dark = null,
        public string $alt = '',
    ) {
        foreach (array_filter([$light, $dark]) as $path) {
            if (! is_file($path) || ! in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), self::EXTENSIONS, true)) {
                throw new InvalidArgumentException("auth.brand image [{$path}] does not exist or is not a supported image.");
            }
        }
    }

    /**
     * Plik wariantu (`light` albo `dark`; ciemny bez własnego pliku to jasny).
     */
    public function path(string $variant): ?string
    {
        return match ($variant) {
            self::LIGHT => $this->light,
            self::DARK => $this->dark ?? $this->light,
            default => null,
        };
    }

    /**
     * Wersja pliku do adresu (unieważnia cache przeglądarki po podmianie obrazu).
     */
    public function version(string $variant): string
    {
        $path = $this->path($variant);

        return $path === null ? '' : substr(hash_file('xxh3', $path) ?: '', 0, 12);
    }
}
