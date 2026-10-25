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

namespace Simplysmart\Mainboard\Legal;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Http\Request;

/**
 * Adres odnośnika „Wstecz” na publicznych stronach dokumentów (polityka cookies, dokumenty prawne; trasy bez sesji):
 * poprzednia strona z nagłówka Referer, gdy jest z tej samej domeny i nie jest tą samą stroną, inaczej strona główna.
 * Obca domena nigdy nie trafia do odnośnika.
 */
final readonly class BackLink
{
    public function __construct(
        private UrlGenerator $url,
    ) {}

    public function for(Request $request): string
    {
        $referer = $request->headers->get('referer');

        if (is_string($referer) && parse_url($referer, PHP_URL_HOST) === $request->getHost() && $referer !== $request->fullUrl()) {
            return $referer;
        }

        return $this->url->to('/');
    }
}
