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
use Illuminate\Validation\ValidationException;
use Simplysmart\Admiro\Panel\Enums\Permission;
use Simplysmart\Admiro\Panel\Http\AdminAudit;
use Simplysmart\Admiro\Panel\Http\AdminContext;
use Simplysmart\Admiro\Contracts\InterfaceLocales;
use Simplysmart\Admiro\Localization\LocalizationSettings;
use Simplysmart\Admiro\Localization\LocalizationSettingsRejected;
use Simplysmart\Admiro\Queries\Dictionaries\DefaultEntries;
use Simplysmart\Admiro\Queries\Dictionaries\LocalizationChoices;

/**
 * Ustawienia systemu: kraj, język i waluta domyślne oraz języki interfejsu (`settings.view` podgląd,
 * `settings.manage` zmiana, w dzienniku zdarzeń).
 */
final readonly class SettingsController
{
    public function __construct(
        private LocalizationChoices $choices,
        private AdminContext $context,
        private Redirector $redirect,
    ) {}

    public function show(DefaultEntries $defaults, InterfaceLocales $locales): View
    {
        return view('admiro::panel.settings.index', [
            'countries' => $this->choices->countries(),
            'currencies' => $this->choices->currencies(),
            'languages' => $this->choices->interfaceCandidates(),
            'country' => $defaults->country(),
            'currency' => $defaults->currency(),
            'language' => $locales->default(),
            'interface' => $locales->enabled(),
            'canManage' => $this->context->can(Permission::SettingsManage),
        ]);
    }

    public function update(Request $request, LocalizationSettings $settings, AdminAudit $audit): RedirectResponse
    {
        $validated = $request->validate([
            'country' => ['required', 'string', 'size:2'],
            'currency' => ['required', 'string', 'size:3'],
            'language' => ['required', 'string', 'max:12'],
            'interface' => ['required', 'array', 'min:1'],
            'interface.*' => ['string', 'max:12'],
        ]);
        $codes = array_values(array_filter((array) $validated['interface'], is_string(...)));
        $country = $this->choices->country(is_string($validated['country']) ? $validated['country'] : '');
        $currency = $this->choices->currency(is_string($validated['currency']) ? $validated['currency'] : '');
        $language = $this->choices->language(is_string($validated['language']) ? $validated['language'] : '');

        if ($country === null || $currency === null || $language === null) {
            throw ValidationException::withMessages(['settings' => text('admiro::admin.settings_rejected')]);
        }

        try {
            $settings->update($country, $language, $currency, $codes);
        } catch (LocalizationSettingsRejected) {
            throw ValidationException::withMessages(['settings' => text('admiro::admin.settings_rejected')]);
        }

        $audit->record('settings updated', context: [
            'country' => $country->alpha_code_2,
            'currency' => $currency->alpha_code_3,
            'language' => $language->code,
            'interface' => implode(',', $codes),
        ]);

        return $this->redirect->route($this->context->zone()->routeName('settings.show'))->with('status', text('admiro::admin.settings_saved'));
    }
}
