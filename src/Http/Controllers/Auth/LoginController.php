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

namespace Simplysmart\Mainboard\Http\Controllers\Auth;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Simplysmart\Mainboard\Auth\SignIn\SignInFlowNavigator;
use Simplysmart\Mainboard\Auth\SignIn\StartSignIn;

/**
 * Formularz adresu e-mail (początek logowania strefy).
 */
final readonly class LoginController
{
    public function create(): View
    {
        return view('mainboard::auth.login');
    }

    public function store(Request $request, StartSignIn $start, SignInFlowNavigator $navigator): RedirectResponse
    {
        $validated = $request->validate(['email' => ['required', 'string', 'email:rfc', 'max:255']]);

        $start->handle(is_string($validated['email']) ? $validated['email'] : '', $request);

        return $navigator->continue($request);
    }
}
