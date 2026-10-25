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
use Simplysmart\Mainboard\Cookies\CookiePolicies;
use Simplysmart\Mainboard\Cookies\CookiePolicyRejected;
use Simplysmart\Mainboard\Queries\Cookies\CookiePolicyVersions;

/**
 * Polityka cookies (`settings.view`): szkic z tytułem i treścią Markdown w każdym języku interfejsu, opublikowane
 * wersje i liczba zgód według decyzji z ostatnich dni; zapis szkicu i publikacja nowej wersji (`settings.manage`,
 * w dzienniku zdarzeń; nowa wersja wymaga ponownej zgody odwiedzających).
 */
final readonly class CookiePolicyController
{
    private const int STATS_DAYS = 30;

    private const int BODY_MAX_LENGTH = 100000;

    public function __construct(
        private AdminContext $context,
        private CookiePolicyVersions $versions,
        private InterfaceLocales $locales,
        private AdminAudit $audit,
        private Redirector $redirect,
    ) {}

    public function index(): View
    {
        return view('admiro::panel.cookie-policy.index', [
            'draft' => $this->versions->draft() ?? $this->versions->current(),
            'isDraft' => $this->versions->draft() !== null,
            'versions' => $this->versions->published(),
            'counts' => $this->versions->consentCounts(self::STATS_DAYS),
            'statsDays' => self::STATS_DAYS,
            'locales' => $this->locales->enabled(),
            'defaultLocale' => $this->locales->default(),
            'canManage' => $this->context->can(Permission::SettingsManage),
        ]);
    }

    public function update(Request $request, CookiePolicies $policies): RedirectResponse
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

        $policies->saveDraft($content);
        $this->audit->record('cookie policy draft saved');

        return $this->back()->with('status', text('admiro::admin.cookie_policy_saved'));
    }

    public function publish(CookiePolicies $policies): RedirectResponse
    {
        try {
            $policy = $policies->publish($this->locales->default());
        } catch (CookiePolicyRejected) {
            return $this->back()->withErrors(['policy' => text('admiro::admin.cookie_policy_rejected')]);
        }

        $this->audit->record('cookie policy published', context: ['version' => $policy->version]);

        return $this->back()->with('status', text('admiro::admin.cookie_policy_published', ['version' => (string) $policy->version]));
    }

    private function back(): RedirectResponse
    {
        return $this->redirect->route($this->context->zone()->routeName('cookie-policy.index'));
    }
}
