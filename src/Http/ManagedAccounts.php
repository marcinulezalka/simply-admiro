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

namespace Simplysmart\Admiro\Http;

use Simplysmart\Mainboard\Contracts\ZoneRegistry;
use Simplysmart\Mainboard\Models\ZoneAccount;
use Simplysmart\Mainboard\Queries\Accounts\ZoneAccounts;
use Simplysmart\Mainboard\Zones\ZoneDefinition;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Strefy z kontami i konta, którymi zarządza panel: strefa po nazwie, konto po tokenie; wszystko inne to 404.
 */
final readonly class ManagedAccounts
{
    public function __construct(
        private ZoneRegistry $zones,
        private ZoneAccounts $accounts,
    ) {}

    /**
     * Strefy z logowaniem, według nazwy.
     *
     * @return list<ZoneDefinition>
     */
    public function zones(): array
    {
        return array_values(array_filter($this->zones->all(), static fn(ZoneDefinition $zone): bool => $zone->requiresLogin()));
    }

    /**
     * @throws NotFoundHttpException
     */
    public function zone(string $name): ZoneDefinition
    {
        if (! $this->zones->has($name) || ! $this->zones->get($name)->requiresLogin()) {
            throw new NotFoundHttpException();
        }

        return $this->zones->get($name);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function account(ZoneDefinition $zone, string $token): ZoneAccount
    {
        $account = $this->accounts->find($zone, $token);

        return $account instanceof ZoneAccount ? $account : throw new NotFoundHttpException();
    }
}
