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

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Validation\Rule;
use Simplysmart\Admiro\Contracts\CurrentAccount;
use Simplysmart\Admiro\Contracts\CurrentZone;
use Simplysmart\Admiro\Enums\AppearanceSetting;
use Simplysmart\Admiro\Http\AccountRoutes;
use Simplysmart\Admiro\Profile\AccountAppearance;

/**
 * Zapis wyglądu w koncie (strefa z `appearance.account`), w aktywnej roli konta (Profile\AccountAppearance): każdy przełącznik strony „Personalizacja” i panelu bocznego
 * wysyła od razu jedno albo kilka ustawień (js/theme.js, JSON → 204), a przywrócenie usuwa wybory konta w tej roli — wraca w niej
 * domyślny wygląd aplikacji. Puste pole (null po ConvertEmptyStringsToNull) to wartość bazowa ustawienia (AppearanceSetting::base(): motyw
 * systemu, kolory i krój bazowy strefy, normalny tekst, pełna szerokość), nie domyślna wartość aplikacji.
 */
final readonly class AppearanceController
{
    public function __construct(
        private CurrentZone $zone,
        private CurrentAccount $current,
        private AccountAppearance $appearance,
        private Redirector $redirect,
    ) {}

    public function update(Request $request): RedirectResponse|Response
    {
        $zone = $this->zone->get();
        $rules = [];

        foreach (AppearanceSetting::cases() as $setting) {
            $rules[$setting->value] = ['sometimes', 'nullable', 'string', Rule::in($setting->values($zone))];
        }

        $validated = $request->validate($rules);
        $choices = [];

        foreach (AppearanceSetting::cases() as $setting) {
            if (array_key_exists($setting->value, $validated)) {
                $value = $validated[$setting->value];
                $choices[$setting->value] = is_string($value) ? $value : $setting->base();
            }
        }

        $this->appearance->save($this->current->require(), $choices);

        return $request->expectsJson() ? new Response(status: Response::HTTP_NO_CONTENT) : $this->back('account.appearance_saved');
    }

    public function destroy(): RedirectResponse
    {
        $this->appearance->reset($this->current->require());

        return $this->back($this->zone->get()->roleContexts ? 'account.appearance_reset_role_done' : 'account.appearance_reset_done');
    }

    private function back(string $status): RedirectResponse
    {
        return $this->redirect->route($this->zone->get()->routeName(AccountRoutes::APPEARANCE))->with('status', text($status));
    }
}
