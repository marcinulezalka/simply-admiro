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
use Simplysmart\Mainboard\Enums\FaviconType;

/**
 * Ikony strony strefy (`favicon` w configu strefy: `ico`, `svg`, `apple` — ścieżki plików, co najmniej jedna).
 * Pliki serwuje trasa `<strefa>.favicon` z wersją w adresie; `x-mainboard::page` w strefie dostaje znaczniki `<link>`.
 */
final readonly class ZoneFavicon
{
    /**
     * @param array<string, string> $files Ścieżki plików według wartości FaviconType.
     *
     * @throws InvalidArgumentException Gdy nie ma żadnego pliku, rodzaj jest nieznany albo pliku nie ma lub ma złe rozszerzenie.
     */
    public function __construct(
        private array $files,
    ) {
        if ($files === []) {
            throw new InvalidArgumentException('favicon needs at least one file (' . implode(', ', array_column(FaviconType::cases(), 'value')) . ').');
        }

        foreach ($files as $type => $path) {
            $extension = FaviconType::tryFrom($type)?->extension() ?? throw new InvalidArgumentException("Unknown favicon type [{$type}].");

            if (! is_file($path) || strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== $extension) {
                throw new InvalidArgumentException("favicon [{$type}] must be an existing .{$extension} file.");
            }
        }
    }

    /**
     * Skonfigurowane rodzaje w kolejności znaczników `<link>`.
     *
     * @return list<FaviconType>
     */
    public function types(): array
    {
        return array_values(array_filter(FaviconType::cases(), fn(FaviconType $type): bool => isset($this->files[$type->value])));
    }

    public function path(FaviconType $type): ?string
    {
        return $this->files[$type->value] ?? null;
    }

    /**
     * Wersja pliku do adresu (unieważnia cache przeglądarki po podmianie ikony).
     */
    public function version(FaviconType $type): string
    {
        $path = $this->path($type);

        return $path === null ? '' : substr(hash_file('xxh3', $path) ?: '', 0, 12);
    }
}
