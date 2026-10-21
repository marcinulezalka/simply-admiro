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

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\View\View;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Contracts\SignInAccount;

/**
 * Strona startowa zalogowanego administratora.
 */
final readonly class DashboardController
{
    public function __invoke(AuthFactory $auth, CurrentZone $zone): View
    {
        $account = $auth->guard($zone->get()->guard)->user();

        return view('admiro::dashboard', [
            'name' => $account instanceof SignInAccount ? $account->displayName() : '',
        ]);
    }
}
