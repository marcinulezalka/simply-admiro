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

namespace Simplysmart\Mainboard\Mail;

use InvalidArgumentException;

/**
 * Kolory e-maili strefy (config `mail.palette`, wartości hex): e-maile mają style inline, bo klienci poczty nie czytają
 * tokenów `--ss-*`, więc strefa podaje kolory wprost. Bez configu — paleta Core z design systemu.
 */
final readonly class MailPalette
{
    private const string HEX = '/^#[0-9a-fA-F]{6}$/';

    public function __construct(
        public string $bg = '#f8fafc',
        public string $surface = '#ffffff',
        public string $border = '#e5e7eb',
        public string $text = '#111827',
        public string $muted = '#6b7280',
        public string $accent = '#1f2937',
        public string $onAccent = '#ffffff',
    ) {
        $colors = ['bg' => $bg, 'surface' => $surface, 'border' => $border, 'text' => $text, 'muted' => $muted, 'accent' => $accent, 'on_accent' => $onAccent];

        foreach ($colors as $name => $color) {
            if (preg_match(self::HEX, $color) !== 1) {
                throw new InvalidArgumentException("mail.palette [{$name}] must be a #rrggbb color.");
            }
        }
    }

    /**
     * Paleta z configu: klucze `bg`, `surface`, `border`, `text`, `muted`, `accent`, `on_accent`; brakujące z Core.
     *
     * @param array<array-key, mixed> $colors
     *
     * @throws InvalidArgumentException Gdy klucz jest nieznany albo kolor nie jest hex.
     */
    public static function fromArray(array $colors): self
    {
        $names = ['bg' => 'bg', 'surface' => 'surface', 'border' => 'border', 'text' => 'text', 'muted' => 'muted', 'accent' => 'accent', 'on_accent' => 'onAccent'];
        $arguments = [];

        foreach ($colors as $key => $color) {
            if (! is_string($key) || ! isset($names[$key]) || ! is_string($color)) {
                throw new InvalidArgumentException('mail.palette needs colors keyed ' . implode(', ', array_keys($names)) . '.');
            }

            $arguments[$names[$key]] = $color;
        }

        return new self(...$arguments);
    }
}
