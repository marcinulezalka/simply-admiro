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

namespace Simplysmart\Mainboard\Zones;

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Simplysmart\Mainboard\Auth\AccountKey;
use Simplysmart\Mainboard\Contracts\CurrentAccount;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Contracts\SignInAccount;
use Simplysmart\Mainboard\Models\ZoneAccount;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Konto z guardu strefy żądania (CurrentZone).
 */
final readonly class RequestAccount implements CurrentAccount
{
    public function __construct(
        private CurrentZone $zone,
        private AuthFactory $auth,
    ) {}

    public function get(): ?SignInAccount
    {
        if (! $this->zone->has() || ! $this->zone->get()->requiresLogin()) {
            return null;
        }

        $account = $this->auth->guard($this->zone->get()->guard)->user();

        return $account instanceof SignInAccount ? $account : null;
    }

    public function id(): int|string|null
    {
        $account = $this->get();

        return $account === null ? null : AccountKey::from($account->getAuthIdentifier());
    }

    public function require(): ZoneAccount
    {
        $account = $this->get();

        return $account instanceof ZoneAccount ? $account : throw new AccessDeniedHttpException();
    }
}
