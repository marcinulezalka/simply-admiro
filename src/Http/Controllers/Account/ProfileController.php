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

namespace Simplysmart\Mainboard\Http\Controllers\Account;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Validation\Rule;
use Simplysmart\Mainboard\Contracts\CurrentAccount;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Enums\AvatarColor;
use Simplysmart\Mainboard\Http\AccountRoutes;
use Simplysmart\Mainboard\Profile\ChangeAvatarColor;

/**
 * Profil zalogowanego konta: dane konta i wybór koloru awatara (paleta AvatarColor).
 */
final readonly class ProfileController
{
    public const string COLOR_FIELD = 'avatar_color';

    public function __construct(
        private CurrentZone $zone,
        private CurrentAccount $current,
        private Redirector $redirect,
    ) {}

    public function show(): View
    {
        return view('mainboard::account.profile', ['profile' => $this->current->require(), 'colors' => AvatarColor::cases()]);
    }

    public function update(Request $request, ChangeAvatarColor $change): RedirectResponse
    {
        $validated = $request->validate([self::COLOR_FIELD => ['required', 'string', Rule::enum(AvatarColor::class)]]);
        $color = AvatarColor::from(is_string($validated[self::COLOR_FIELD]) ? $validated[self::COLOR_FIELD] : '');

        $change->handle($this->current->require(), $color);

        return $this->redirect->route($this->zone->get()->routeName(AccountRoutes::PROFILE))->with('status', text('account.avatar_color_saved'));
    }
}
