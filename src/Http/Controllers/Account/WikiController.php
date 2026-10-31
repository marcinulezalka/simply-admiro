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

namespace Simplysmart\Admiro\Http\Controllers\Account;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Simplysmart\Admiro\Wiki\Wiki;

/**
 * Wiki strefy dla zalogowanego konta (`account.wiki`): wszystkie wpisy Wiki\Wiki — Core i paczek — jako porady i Q&A,
 * z wyszukiwaniem (`q`: pojęcie, definicja, pytanie, „Jak korzystać”, przykład); tu prowadzi komenda `wiki:` pola komend.
 */
final readonly class WikiController
{
    public const string SEARCH_FIELD = 'q';

    public function __invoke(Request $request, Wiki $wiki): View
    {
        $term = $request->string(self::SEARCH_FIELD)->trim()->toString();

        return view('admiro::account.wiki', ['term' => $term, 'entries' => $wiki->search($term)]);
    }
}
