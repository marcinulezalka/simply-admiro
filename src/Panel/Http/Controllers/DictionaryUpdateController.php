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
use Simplysmart\Admiro\Auth\AccountKey;
use Simplysmart\Admiro\DictionaryUpdates\DictionaryUpdateRejected;
use Simplysmart\Admiro\DictionaryUpdates\DictionaryUpdateRequests;
use Simplysmart\Admiro\DictionaryUpdates\DictionaryUpdaters;
use Simplysmart\Admiro\Models\DictionaryUpdateRequest;
use Simplysmart\Admiro\Models\Organization;
use Simplysmart\Admiro\Panel\Enums\Permission;
use Simplysmart\Admiro\Panel\Http\AdminAudit;
use Simplysmart\Admiro\Panel\Http\AdminContext;
use Simplysmart\Admiro\Queries\DictionaryUpdates\DictionaryUpdateRequestList;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Prośby stref o aktualizację słowników (`settings.view`): lista z prośbami czekającymi na górze — słownik, strefa
 * i organizacja proszącego, data, stan i wynik — oraz zarejestrowane słowniki; zatwierdzenie (zadanie aktualizacji
 * w kolejce) albo odrzucenie (`settings.manage`, w dzienniku zdarzeń).
 */
final readonly class DictionaryUpdateController
{
    public function __construct(
        private AdminContext $context,
        private DictionaryUpdateRequestList $requests,
        private DictionaryUpdateRequests $updates,
        private AdminAudit $audit,
        private Redirector $redirect,
    ) {}

    public function index(DictionaryUpdaters $updaters): View
    {
        $requests = $this->requests->recent();
        $organizationIds = array_values(array_unique(array_filter($requests->pluck('organization_id')->all(), is_int(...))));

        return view('admiro::panel.dictionary-updates.index', [
            'requests' => $requests,
            'organizations' => Organization::query()->whereIn('id', $organizationIds)->pluck('name', 'id')->all(),
            'dictionaries' => $updaters->all(),
            'canManage' => $this->context->can(Permission::SettingsManage),
        ]);
    }

    public function approve(string $dictionaryRequest): RedirectResponse
    {
        $record = $this->find($dictionaryRequest);

        try {
            $this->updates->approve($record, AccountKey::of($this->context->admin()));
        } catch (DictionaryUpdateRejected) {
            return $this->back()->withErrors(['dictionary_update' => text('admiro::admin.dictionary_updates.decided')]);
        }

        $this->audit->record('dictionary update approved', context: ['dictionary' => $record->dictionary]);

        return $this->back()->with('status', text('admiro::admin.dictionary_updates.approved', ['dictionary' => $record->name]));
    }

    public function reject(string $dictionaryRequest): RedirectResponse
    {
        $record = $this->find($dictionaryRequest);

        try {
            $this->updates->reject($record, AccountKey::of($this->context->admin()));
        } catch (DictionaryUpdateRejected) {
            return $this->back()->withErrors(['dictionary_update' => text('admiro::admin.dictionary_updates.decided')]);
        }

        $this->audit->record('dictionary update rejected', context: ['dictionary' => $record->dictionary]);

        return $this->back()->with('status', text('admiro::admin.dictionary_updates.rejected', ['dictionary' => $record->name]));
    }

    private function find(string $token): DictionaryUpdateRequest
    {
        return $this->requests->find($token) ?? throw new NotFoundHttpException();
    }

    private function back(): RedirectResponse
    {
        return $this->redirect->route('admiro.dictionary-updates.index');
    }
}
