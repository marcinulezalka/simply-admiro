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

namespace Simplysmart\Mainboard\Auth\SignIn;

use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;
use Simplysmart\Mainboard\Auth\Events\SignedOut;
use Simplysmart\Mainboard\Contracts\CurrentZone;

/**
 * Wylogowanie z bieżącej strefy: unieważnia tylko jej sesję (inne strefy zostają zalogowane).
 */
final readonly class SignOut
{
    public function __construct(
        private AuthManager $auth,
        private CurrentZone $zone,
        private Dispatcher $events,
    ) {}

    public function handle(Request $request): void
    {
        $zone = $this->zone->get();
        $guard = $this->auth->guard($zone->guard);
        $accountId = $guard->id();

        if ($guard instanceof StatefulGuard) {
            $guard->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $this->events->dispatch(new SignedOut($zone->name, is_int($accountId) || is_string($accountId) ? $accountId : null));
    }
}
