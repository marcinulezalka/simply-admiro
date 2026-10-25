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

namespace Simplysmart\Admiro\Zones;

use InvalidArgumentException;
use Simplysmart\Admiro\Enums\BrandAsset;

/**
 * Marka strefy z configu (`brand`: `logo`, `signet` — ścieżki plików PNG/JPG, co najmniej jeden). Logo strefy trafia
 * do e-maili, gdy organizacja nie ma własnego; ikona strony strefy zostaje w `favicon` ({@see ZoneFavicon}).
 */
final readonly class ZoneBrand
{
    /**
     * @param array<string, string> $files Ścieżki plików według wartości BrandAsset.
     *
     * @throws InvalidArgumentException Gdy nie ma żadnego pliku, rodzaj jest nieznany albo pliku nie ma lub ma złe rozszerzenie.
     */
    public function __construct(
        private array $files,
    ) {
        if ($files === []) {
            throw new InvalidArgumentException('brand needs at least one file (' . BrandAsset::Logo->value . ', ' . BrandAsset::Signet->value . ').');
        }

        foreach ($files as $type => $path) {
            $asset = BrandAsset::tryFrom($type);

            if ($asset === null || $asset === BrandAsset::Favicon) {
                throw new InvalidArgumentException("Unknown brand file [{$type}] (favicon has its own `favicon` key).");
            }

            if (! is_file($path) || ! $asset->accepts(pathinfo($path, PATHINFO_EXTENSION))) {
                throw new InvalidArgumentException("brand [{$type}] must be an existing ." . implode('/.', $asset->extensions()) . ' file.');
            }
        }
    }

    public function path(BrandAsset $asset): ?string
    {
        return $this->files[$asset->value] ?? null;
    }
}
