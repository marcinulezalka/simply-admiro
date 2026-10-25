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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Simplysmart\Admiro\Panel\Enums\Permission;
use Simplysmart\Admiro\Panel\Http\AdminAudit;
use Simplysmart\Admiro\Panel\Http\AdminContext;
use Simplysmart\Mainboard\Contracts\InterfaceLocales;
use Simplysmart\Mainboard\Enums\LegalDocumentType;
use Simplysmart\Mainboard\Legal\LegalDocumentRejected;
use Simplysmart\Mainboard\Legal\LegalDocuments;
use Simplysmart\Mainboard\Queries\Legal\LegalDocumentVersions;

/**
 * Dokumenty prawne Core (`settings.view`; polityka prywatności, klauzula RODO — dokument z adresu, nieznany → 404):
 * szkic z tytułem i treścią Markdown w każdym języku interfejsu i opublikowane wersje; zapis szkicu i publikacja
 * nowej wersji (`settings.manage`, w dzienniku zdarzeń).
 */
final readonly class LegalDocumentController
{
    private const int BODY_MAX_LENGTH = 100000;

    public function __construct(
        private AdminContext $context,
        private LegalDocumentVersions $versions,
        private InterfaceLocales $locales,
        private AdminAudit $audit,
        private Redirector $redirect,
    ) {}

    public function index(LegalDocumentType $document): View
    {
        $draft = $this->versions->draft($document);

        return view('admiro::panel.legal-documents.index', [
            'document' => $document,
            'draft' => $draft ?? $this->versions->current($document),
            'isDraft' => $draft !== null,
            'versions' => $this->versions->published($document),
            'locales' => $this->locales->enabled(),
            'defaultLocale' => $this->locales->default(),
            'canManage' => $this->context->can(Permission::SettingsManage),
        ]);
    }

    public function update(Request $request, LegalDocumentType $document, LegalDocuments $documents): RedirectResponse
    {
        $locales = $this->locales->enabled();
        $rules = [];

        foreach ($locales as $locale) {
            $rules["title.{$locale}"] = ['nullable', 'string', 'max:255'];
            $rules["body.{$locale}"] = ['nullable', 'string', 'max:' . self::BODY_MAX_LENGTH];
        }

        $validated = $request->validate($rules);
        $titles = is_array($validated['title'] ?? null) ? $validated['title'] : [];
        $bodies = is_array($validated['body'] ?? null) ? $validated['body'] : [];
        $content = [];

        foreach ($locales as $locale) {
            $content[$locale] = [
                'title' => is_string($titles[$locale] ?? null) ? trim($titles[$locale]) : '',
                'body' => is_string($bodies[$locale] ?? null) ? $bodies[$locale] : '',
            ];
        }

        $documents->saveDraft($document, $content);
        $this->audit->record('legal document draft saved', context: ['document' => $document->value]);

        return $this->back($document)->with('status', text('admiro::admin.legal_document_saved', ['document' => $document->label()]));
    }

    public function publish(LegalDocumentType $document, LegalDocuments $documents): RedirectResponse
    {
        try {
            $published = $documents->publish($document, $this->locales->default());
        } catch (LegalDocumentRejected) {
            return $this->back($document)->withErrors(['document' => text('admiro::admin.legal_document_rejected')]);
        }

        $this->audit->record('legal document published', context: ['document' => $document->value, 'version' => $published->version]);

        return $this->back($document)->with('status', text('admiro::admin.legal_document_published', ['document' => $document->label(), 'version' => (string) $published->version]));
    }

    private function back(LegalDocumentType $document): RedirectResponse
    {
        return $this->redirect->route($this->context->zone()->routeName('legal-documents.index'), ['document' => $document->value]);
    }
}
