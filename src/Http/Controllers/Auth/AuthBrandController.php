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

namespace Simplysmart\Admiro\Http\Controllers\Auth;

use Simplysmart\Admiro\Contracts\CurrentZone;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Plik banera logowania strefy (AuthBrand): tylko skonfigurowane pliki, z długim cache (adres zawiera wersję).
 */
final readonly class AuthBrandController
{
    private const int MAX_AGE_SECONDS = 31_536_000;

    public function __invoke(CurrentZone $zone, string $variant): BinaryFileResponse
    {
        $path = $zone->get()->auth()->brand?->path($variant) ?? throw new NotFoundHttpException();

        return response()->file($path, ['Cache-Control' => 'public, max-age=' . self::MAX_AGE_SECONDS . ', immutable']);
    }
}
