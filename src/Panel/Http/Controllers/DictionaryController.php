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
use Illuminate\Routing\Redirector;
use Simplysmart\Admiro\Panel\Enums\Permission;
use Simplysmart\Admiro\Panel\Http\AdminAudit;
use Simplysmart\Admiro\Panel\Http\AdminContext;
use Simplysmart\Admiro\Database\ColumnNames;
use Simplysmart\Admiro\Localization\Dictionary;
use Simplysmart\Admiro\Localization\DictionaryChangeRejected;
use Simplysmart\Admiro\Localization\DictionaryManagement;
use Simplysmart\Admiro\Queries\Dictionaries\DictionaryList;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Słowniki krajów, języków i walut (`settings.view`) z włączaniem i wyłączaniem wpisów (`settings.manage`);
 * wpisu domyślnego ani języka interfejsu nie da się wyłączyć.
 */
final readonly class DictionaryController
{
    public function __construct(
        private DictionaryList $dictionaries,
        private AdminContext $context,
        private Redirector $redirect,
    ) {}

    public function index(string $dictionary): View
    {
        $kind = Dictionary::tryFrom($dictionary) ?? throw new NotFoundHttpException();

        return view('admiro::panel.dictionaries.index', [
            'dictionary' => $kind,
            'dictionaries' => Dictionary::cases(),
            'entries' => $this->dictionaries->entries($kind),
            'codeColumn' => $kind->model()::codeColumn(),
            'canManage' => $this->context->can(Permission::SettingsManage),
        ]);
    }

    public function toggle(string $dictionary, string $code, DictionaryManagement $management, AdminAudit $audit): RedirectResponse
    {
        $kind = Dictionary::tryFrom($dictionary) ?? throw new NotFoundHttpException();
        $entry = $this->dictionaries->find($kind, $code) ?? throw new NotFoundHttpException();
        $active = $entry->getAttribute(ColumnNames::IS_ACTIVE) !== true;
        $back = $this->redirect->route($this->context->zone()->routeName('dictionaries.index'), ['dictionary' => $kind->value]);

        try {
            $management->setActive($entry, $active);
        } catch (DictionaryChangeRejected) {
            return $back->withErrors(['dictionary' => text('admiro::admin.dictionary_rejected')]);
        }

        $audit->record($active ? 'dictionary entry enabled' : 'dictionary entry disabled', context: ['dictionary' => $kind->value, 'code' => $code]);

        return $back->with('status', text('admiro::admin.dictionary_saved'));
    }
}
