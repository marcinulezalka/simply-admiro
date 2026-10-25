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

namespace Simplysmart\Admiro\Http\Controllers;

use Simplysmart\Admiro\Contracts\CurrentZone;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Plik fontu strefy (Zones\ZoneFonts): tylko skonfigurowane pliki, z długim cache (adres zawiera wersję).
 */
final readonly class ZoneFontController
{
    private const int MAX_AGE_SECONDS = 31_536_000;

    public function __invoke(CurrentZone $zone, string $font): BinaryFileResponse
    {
        $path = $zone->get()->fonts?->path($font);

        return response()->file($path ?? throw new NotFoundHttpException(), [
            'Content-Type' => 'font/woff2',
            'Cache-Control' => 'public, max-age=' . self::MAX_AGE_SECONDS . ', immutable',
        ]);
    }
}
