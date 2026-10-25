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

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Validation\Rule;
use Simplysmart\Admiro\Contracts\CurrentAccount;
use Simplysmart\Admiro\Contracts\CurrentOrganization;
use Simplysmart\Admiro\Contracts\CurrentZone;
use Simplysmart\Admiro\Enums\AvatarColor;
use Simplysmart\Admiro\Http\AccountRoutes;
use Simplysmart\Admiro\Localization\LocaleRouting;
use Simplysmart\Admiro\Models\Organization;
use Simplysmart\Admiro\Profile\AccountLocale;
use Simplysmart\Admiro\Profile\UpdateProfile;
use Simplysmart\Admiro\Queries\Organizations\ActiveMemberships;
use Simplysmart\Admiro\Queries\Permissions\ZoneRoleNames;

/**
 * Profil zalogowanego konta: imię i nazwisko, adres e-mail (tylko do odczytu), język interfejsu (gdy włączonych jest
 * kilka), kolor awatara (paleta AvatarColor) i w strefie z `teams` organizacje konta z rolami i przełącznikiem.
 * Zapis z innym językiem wraca na profil w tym języku.
 */
final readonly class ProfileController
{
    public const string NAME_FIELD = 'name';

    public const string COLOR_FIELD = 'avatar_color';

    public const string LOCALE_FIELD = 'locale';

    private const int NAME_MAX_LENGTH = 255;

    public function __construct(
        private CurrentZone $zone,
        private CurrentAccount $current,
        private Redirector $redirect,
        private AccountLocale $locale,
    ) {}

    public function show(ActiveMemberships $memberships, ZoneRoleNames $roles, CurrentOrganization $organization): View
    {
        $zone = $this->zone->get();
        $profile = $this->current->require();

        return view('admiro::account.profile', [
            'profile' => $profile,
            'colors' => AvatarColor::cases(),
            'locales' => $this->locale->isSelectable() ? $this->locale->choices() : [],
            'currentLocale' => $this->locale->of($profile),
            'organizations' => $zone->teams ? $memberships->organizations($profile)->map(static fn(Organization $item): array => [
                'organization' => $item,
                'roles' => array_map($zone->roleLabel(...), $roles->of($profile, $item->id)),
                'current' => $item->id === $organization->id(),
            ])->values()->all() : [],
        ]);
    }

    public function update(Request $request, UpdateProfile $update): RedirectResponse
    {
        $locales = $this->locale->isSelectable() ? $this->locale->choices() : [];
        $validated = $request->validate([
            self::NAME_FIELD => ['required', 'string', 'max:' . self::NAME_MAX_LENGTH],
            self::COLOR_FIELD => ['required', 'string', Rule::enum(AvatarColor::class)],
            self::LOCALE_FIELD => ['nullable', 'string', Rule::in($locales)],
        ]);
        $locale = is_string($validated[self::LOCALE_FIELD] ?? null) ? $validated[self::LOCALE_FIELD] : null;

        $update->handle(
            $this->current->require(),
            trim(is_string($validated[self::NAME_FIELD]) ? $validated[self::NAME_FIELD] : ''),
            AvatarColor::from(is_string($validated[self::COLOR_FIELD]) ? $validated[self::COLOR_FIELD] : ''),
            $locale,
        );

        return $this->redirect->route($this->zone->get()->routeName(AccountRoutes::PROFILE), $locale !== null ? [LocaleRouting::PARAMETER => $locale] : [])->with('status', text('account.profile_saved'));
    }
}
