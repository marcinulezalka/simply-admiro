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

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Simplysmart\Mainboard\Contracts\InterfaceLocales;
use Simplysmart\Mainboard\Cookies\PolicyMarkdown;
use Simplysmart\Mainboard\Enums\LegalDocumentType;
use Simplysmart\Mainboard\Legal\BackLink;
use Simplysmart\Mainboard\Legal\LegalLinks;
use Simplysmart\Mainboard\Queries\Legal\LegalDocumentVersions;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Publiczne strony dokumentów prawnych Core (bez sesji): bieżąca wersja polityki prywatności albo klauzuli RODO
 * w języku żądania (albo domyślnym), z odnośnikiem „Wstecz” i kartą „Zobacz także”; bez opublikowanej wersji 404.
 */
final readonly class LegalDocumentController
{
    public function __construct(
        private LegalDocumentVersions $versions,
        private InterfaceLocales $locales,
        private LegalLinks $links,
        private BackLink $back,
    ) {}

    public function privacy(Request $request): View
    {
        return $this->show($request, LegalDocumentType::Privacy);
    }

    public function gdpr(Request $request): View
    {
        return $this->show($request, LegalDocumentType::Gdpr);
    }

    private function show(Request $request, LegalDocumentType $type): View
    {
        $document = $this->versions->current($type) ?? throw new NotFoundHttpException();
        $content = $document->content(App::getLocale());

        if (trim($content['body']) === '') {
            $content = $document->content($this->locales->default());
        }

        return view('mainboard::legal.show', [
            'type' => $type,
            'document' => $document,
            'title' => $content['title'] !== '' ? $content['title'] : $type->label(),
            'html' => PolicyMarkdown::html($content['body']),
            'back' => $this->back->for($request),
            'related' => $this->links->related($type->value),
        ]);
    }
}
