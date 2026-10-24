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

namespace Simplysmart\Mainboard\Zones;

use InvalidArgumentException;

/**
 * Fonty strefy (`fonts` w configu strefy: klucz pliku → `family`, `weight`, `style`, `file` (.woff2), opcjonalnie
 * `unicode_range`). Pliki serwuje trasa `<strefa>.font` z wersją w adresie (CSP `font-src 'self'`); `x-mainboard::page`
 * w strefie dostaje reguły `@font-face`, a arkusz strefy (`stylesheets`) przypisuje rodziny tokenom (np. --ss-font-sans).
 */
final readonly class ZoneFonts
{
    private const string KEY_PATTERN = '/^[a-z0-9][a-z0-9-]*$/';

    private const string FAMILY_PATTERN = '/^[A-Za-z0-9 -]+$/';

    private const string UNICODE_RANGE_PATTERN = '/^U\+[0-9A-Fa-f?]+(-[0-9A-Fa-f]+)?(, ?U\+[0-9A-Fa-f?]+(-[0-9A-Fa-f]+)?)*$/';

    private const array STYLES = ['normal', 'italic'];

    /**
     * @param array<string, array{family: string, weight: int, style: string, file: string, unicode_range: ?string}> $faces
     *
     * @throws InvalidArgumentException Gdy nie ma żadnego fontu albo klucz, rodzina, grubość, styl, zakres lub plik są niepoprawne.
     */
    public function __construct(
        private array $faces,
    ) {
        if ($faces === []) {
            throw new InvalidArgumentException('fonts needs at least one font file.');
        }

        foreach ($faces as $key => $face) {
            if (preg_match(self::KEY_PATTERN, $key) !== 1) {
                throw new InvalidArgumentException("fonts key [{$key}] must be lowercase letters, digits and dashes.");
            }

            if (preg_match(self::FAMILY_PATTERN, $face['family']) !== 1) {
                throw new InvalidArgumentException("fonts [{$key}] family must be letters, digits, spaces and dashes.");
            }

            if ($face['weight'] < 100 || $face['weight'] > 900 || ! in_array($face['style'], self::STYLES, true)) {
                throw new InvalidArgumentException("fonts [{$key}] needs a weight 100–900 and style normal or italic.");
            }

            if ($face['unicode_range'] !== null && preg_match(self::UNICODE_RANGE_PATTERN, $face['unicode_range']) !== 1) {
                throw new InvalidArgumentException("fonts [{$key}] unicode_range must be a list of U+ ranges.");
            }

            if (! is_file($face['file']) || strtolower(pathinfo($face['file'], PATHINFO_EXTENSION)) !== 'woff2') {
                throw new InvalidArgumentException("fonts [{$key}] file must be an existing .woff2 file.");
            }
        }
    }

    /**
     * Klucze plików w kolejności z configu (parametr trasy `<strefa>.font`).
     *
     * @return list<string>
     */
    public function keys(): array
    {
        return array_keys($this->faces);
    }

    public function path(string $key): ?string
    {
        return $this->faces[$key]['file'] ?? null;
    }

    /**
     * Wersja pliku do adresu (unieważnia cache przeglądarki po podmianie fontu).
     */
    public function version(string $key): string
    {
        $path = $this->path($key);

        return $path === null ? '' : substr(hash_file('xxh3', $path) ?: '', 0, 12);
    }

    /**
     * Reguły `@font-face` z adresami plików z $url (klucz i wersja → adres).
     *
     * @param callable(string, string): string $url
     */
    public function fontFaceCss(callable $url): string
    {
        $rules = [];

        foreach ($this->faces as $key => $face) {
            $range = $face['unicode_range'] === null ? '' : ' unicode-range: ' . $face['unicode_range'] . ';';
            $rules[] = sprintf(
                '@font-face { font-family: "%s"; font-style: %s; font-weight: %d; font-display: swap; src: url("%s") format("woff2");%s }',
                $face['family'],
                $face['style'],
                $face['weight'],
                $url($key, $this->version($key)),
                $range,
            );
        }

        return implode("\n", $rules);
    }
}
