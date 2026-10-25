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

namespace Simplysmart\Admiro\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Simplysmart\Admiro\Security\Honeypot\HoneypotTracker;
use Simplysmart\Admiro\Security\Honeypot\HoneypotTrap;

/**
 * Fałszywe logowanie pod adresami pułapek: wygląda jak logowanie strefy, odsyła formularz na ten sam adres
 * i zawsze „nie udaje się”. Działa bez sesji i ciasteczek.
 */
final readonly class HoneypotController
{
    public function show(Request $request, HoneypotTracker $tracker): View
    {
        $tracker->record($request, HoneypotTrap::RouteVisit);

        return view('admiro::honeypot.login', ['failed' => false, 'email' => null]);
    }

    public function trap(Request $request, HoneypotTracker $tracker): View
    {
        $tracker->record($request, HoneypotTrap::RouteSubmit);
        $email = $request->input('email');

        return view('admiro::honeypot.login', ['failed' => true, 'email' => is_string($email) ? mb_substr($email, 0, 255) : null]);
    }
}
