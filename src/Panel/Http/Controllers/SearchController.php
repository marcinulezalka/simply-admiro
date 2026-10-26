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
use Simplysmart\Admiro\Panel\Http\AdminContext;
use Simplysmart\Admiro\Panel\Search\AdmiroSearch;

/**
 * Globalna wyszukiwarka strefy administratorów: strona wszystkich wyników (`q`, zaznaczone zakresy `in[]`) i podpowiedzi
 * w trakcie pisania (fragment HTML `admiro::search.suggestions` dla js/global-search.js). Wyniki daje AdmiroSearch,
 * tylko z zakresów dozwolonych administratorowi.
 */
final readonly class SearchController
{
    public function __construct(
        private AdmiroSearch $search,
        private AdminContext $context,
    ) {}

    public function index(Request $request): View
    {
        $term = $request->string('q')->trim()->toString();

        return view('admiro::panel.search', [
            'term' => $term,
            'groups' => $this->search->groups($term, $this->search->scopes($this->requestedScopes($request)), AdmiroSearch::PAGE),
        ]);
    }

    public function suggest(Request $request): View
    {
        $term = $request->string('q')->trim()->toString();
        $scopes = $this->requestedScopes($request);

        return view('admiro::search.suggestions', [
            'groups' => $this->search->groups($term, $this->search->scopes($scopes), AdmiroSearch::SUGGESTIONS),
            'allUrl' => route($this->context->zone()->routeName('search'), array_filter(['q' => $term, 'in' => $scopes])),
        ]);
    }

    /**
     * @return list<string>|null Zaznaczone zakresy albo null, gdy żądanie ich nie podaje (wszystkie dozwolone).
     */
    private function requestedScopes(Request $request): ?array
    {
        if (! $request->has('in')) {
            return null;
        }

        $scopes = $request->input('in');

        return is_array($scopes) ? array_values(array_filter($scopes, is_string(...))) : [];
    }
}
