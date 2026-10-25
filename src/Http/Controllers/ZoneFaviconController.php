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

namespace Simplysmart\Mainboard\Http\Controllers;

use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Enums\FaviconType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Plik ikony strony strefy (Zones\ZoneFavicon): tylko skonfigurowane pliki, z długim cache (adres zawiera wersję).
 */
final readonly class ZoneFaviconController
{
    private const int MAX_AGE_SECONDS = 31_536_000;

    public function __invoke(CurrentZone $zone, string $type): BinaryFileResponse
    {
        $favicon = FaviconType::tryFrom($type);
        $path = $favicon === null ? null : $zone->get()->favicon?->path($favicon);

        return response()->file($path ?? throw new NotFoundHttpException(), ['Cache-Control' => 'public, max-age=' . self::MAX_AGE_SECONDS . ', immutable']);
    }
}
