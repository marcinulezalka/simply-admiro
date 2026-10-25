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

namespace Simplysmart\Admiro\Permissions;

use Illuminate\Contracts\Session\Session;
use Illuminate\Database\Eloquent\Model;
use Simplysmart\Admiro\Models\Organization;
use Simplysmart\Admiro\Queries\Organizations\ActiveMemberships;
use Simplysmart\Admiro\Zones\ZoneDefinition;

/**
 * Wybiera organizację żądania. Strefa bez zespołów: zawsze organizacja operatora. Strefa z zespołami:
 * organizacja wybrana przez konto (sesja strefy), o ile nadal jest jego aktywnym członkostwem, inaczej
 * pierwsza aktywna organizacja konta; bez konta lub bez członkostw: brak organizacji.
 */
final readonly class OrganizationSelector
{
    public const string SESSION_KEY = 'admiro.organization';

    public function __construct(
        private ActiveMemberships $memberships,
    ) {}

    public function select(ZoneDefinition $zone, ?Model $account, Session $session): ?int
    {
        if (! $zone->teams) {
            return Organization::OPERATOR_ID;
        }

        if ($account === null) {
            return null;
        }

        $memberships = $this->memberships->ids($account);
        $chosen = $session->get(self::SESSION_KEY);

        return is_int($chosen) && in_array($chosen, $memberships, true) ? $chosen : ($memberships[0] ?? null);
    }

    /**
     * Zapamiętuje wybór konta w sesji strefy; organizacja spoza aktywnych członkostw jest odrzucana.
     */
    public function choose(Model $account, Organization $organization, Session $session): bool
    {
        $id = $organization->getKey();

        if (! is_int($id) || ! $this->memberships->includes($account, $id)) {
            return false;
        }

        $session->put(self::SESSION_KEY, $id);

        return true;
    }
}
