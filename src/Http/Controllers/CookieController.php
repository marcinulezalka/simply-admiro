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

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;
use Simplysmart\Admiro\Contracts\CookieCatalog;
use Simplysmart\Admiro\Contracts\InterfaceLocales;
use Simplysmart\Admiro\Cookies\ConsentRegistry;
use Simplysmart\Admiro\Cookies\ConsentRejected;
use Simplysmart\Admiro\Cookies\PolicyMarkdown;
use Simplysmart\Admiro\Enums\ConsentDecision;
use Simplysmart\Admiro\Enums\CookieCategory;
use Simplysmart\Admiro\Enums\LegalDocumentType;
use Simplysmart\Admiro\Legal\BackLink;
use Simplysmart\Admiro\Legal\LegalLinks;
use Simplysmart\Admiro\Queries\Legal\LegalDocumentVersions;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Publiczne trasy Core dla cookies (bez sesji): strona bieżącej polityki cookies w języku żądania (albo domyślnym)
 * z listą cookies według kategorii, odnośnikiem „Wstecz” i kartą „Zobacz także” (opublikowane dokumenty prawne) oraz zapis zgody z banera w rejestrze (JSON, limit żądań, bez IP).
 */
final readonly class CookieController
{
    public function __construct(
        private LegalDocumentVersions $versions,
    ) {}

    public function policy(Request $request, CookieCatalog $catalog, InterfaceLocales $locales, LegalLinks $links, BackLink $back): View
    {
        $policy = $this->versions->current(LegalDocumentType::Cookies) ?? throw new NotFoundHttpException();
        $content = $policy->content(App::getLocale());

        if (trim($content['body']) === '') {
            $content = $policy->content($locales->default());
        }

        return view('admiro::cookies.policy', [
            'policy' => $policy,
            'title' => $content['title'],
            'html' => PolicyMarkdown::html($content['body']),
            'cookies' => $catalog->all(),
            'back' => $back->for($request),
            'related' => $links->related(LegalDocumentType::Cookies),
        ]);
    }

    public function consent(Request $request, ConsentRegistry $registry): JsonResponse
    {
        $validated = $request->validate([
            'consent_id' => ['required', 'string', 'size:36'],
            'version' => ['required', 'integer', 'min:0'],
            'decision' => ['required', Rule::enum(ConsentDecision::class)],
            'categories' => ['present', 'array', 'max:' . count(CookieCategory::cases())],
            'categories.*' => [Rule::enum(CookieCategory::class)],
        ]);
        $categories = array_values(array_map(static fn(mixed $value): CookieCategory => CookieCategory::from(is_string($value) ? $value : ''), is_array($validated['categories']) ? $validated['categories'] : []));

        try {
            $registry->record(
                is_string($validated['consent_id']) ? $validated['consent_id'] : '',
                is_numeric($validated['version']) ? (int) $validated['version'] : -1,
                $this->versions->current(LegalDocumentType::Cookies)->version ?? 0,
                ConsentDecision::from(is_string($validated['decision']) ? $validated['decision'] : ''),
                $categories,
            );
        } catch (ConsentRejected) {
            return new JsonResponse(['recorded' => false], 422);
        }

        return new JsonResponse(['recorded' => true], 201);
    }
}
