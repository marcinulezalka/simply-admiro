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

use BladeUI\Icons\Exceptions\SvgNotFound;
use BladeUI\Icons\Factory;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Plik SVG flagi kraju lub języka (kod `flag_icon`, flag-icons 4:3 z outhebox/blade-flags) dla obrazka flagi wczytywanego
 * dopiero po wyborze kraju (x-admiro::flag-select, js/flag-select.js) — strona nie osadza flag wszystkich opcji. Adres tylko
 * ze wzorem PATTERN (inny — 404); kod spoza pakietu dostaje jego neutralną flagę `xx`, a gdy pakiet nie ma zastępczej — 404.
 * Długi cache (flaga kodu się nie zmienia).
 */
final readonly class FlagController
{
    private const int MAX_AGE_SECONDS = 31_536_000;

    /** Kod w szablonie adresu flagi (x-admiro::flag-select), który js/flag-select.js zastępuje kodem wybranej opcji. */
    public const string FLAG_PLACEHOLDER = 'flagcode';

    /** Wzór kodu flagi w adresie (np. `pl`, `gb-eng`). */
    public const string PATTERN = '[a-z0-9]+(?:-[a-z0-9]+)*';

    public function __invoke(Factory $icons, string $flag): Response
    {
        try {
            $svg = $icons->svg('flag-flat-country-' . $flag)->contents();
        } catch (SvgNotFound) {
            throw new NotFoundHttpException();
        }

        return new Response($svg, 200, ['Content-Type' => 'image/svg+xml', 'Cache-Control' => 'public, max-age=' . self::MAX_AGE_SECONDS . ', immutable']);
    }
}
