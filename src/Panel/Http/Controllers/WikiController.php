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

namespace Simplysmart\Admiro\Panel\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Simplysmart\Admiro\Wiki\Wiki;

/**
 * Strona słownika pojęć (`wiki`, każdy zalogowany administrator): wszystkie wpisy Wiki\Wiki — Core i paczek — jako
 * porady i Q&A, z wyszukiwaniem (`q`: pojęcie, definicja, przykład, pytanie).
 */
final readonly class WikiController
{
    public function __invoke(Request $request, Wiki $wiki): View
    {
        $term = $request->string('q')->trim()->toString();

        return view('admiro::panel.wiki.index', ['term' => $term, 'entries' => $wiki->search($term)]);
    }
}
