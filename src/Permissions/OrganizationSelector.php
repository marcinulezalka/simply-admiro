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
use Simplysmart\Admiro\Models\ZoneAccount;
use Simplysmart\Admiro\Queries\Organizations\ActiveMemberships;
use Simplysmart\Admiro\Queries\Permissions\ZoneRoleNames;
use Simplysmart\Admiro\Zones\ZoneDefinition;

/**
 * Wybiera organizację żądania. Strefa bez zespołów: zawsze organizacja operatora. Strefa z zespołami:
 * organizacja wybrana przez konto (sesja strefy), o ile nadal jest jego aktywnym członkostwem, inaczej domyślny
 * kontekst — najstarsze członkostwo, w którym konto ma rolę strefy (a bez takiego najstarsze członkostwo) —
 * zapamiętany w sesji, więc konto zaraz po zalogowaniu działa w kontekście z rolą, a nowe członkostwo nadane
 * w trakcie sesji nie zmienia go samo; bez konta lub bez członkostw: brak organizacji.
 */
final readonly class OrganizationSelector
{
    public const string SESSION_KEY = 'admiro.organization';

    public function __construct(
        private ActiveMemberships $memberships,
        private ZoneRoleNames $roles,
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

        if (is_int($chosen) && in_array($chosen, $memberships, true)) {
            return $chosen;
        }

        $default = $this->withRole($account, $memberships) ?? $memberships[0] ?? null;

        if ($default !== null) {
            $session->put(self::SESSION_KEY, $default);
        }

        return $default;
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

    /**
     * Zapamiętuje w sesji strefy organizację rozpoznaną z klucza kontekstu w adresie (Zones\ContextKeys sprawdził już
     * członkostwo konta).
     */
    public function remember(int $organizationId, Session $session): void
    {
        $session->put(self::SESSION_KEY, $organizationId);
    }

    /**
     * Najstarsze członkostwo, w którym konto strefy ma rolę; null bez takiego (albo dla konta bez ról strefy).
     *
     * @param list<int> $memberships
     */
    private function withRole(Model $account, array $memberships): ?int
    {
        if (! $account instanceof ZoneAccount) {
            return null;
        }

        foreach ($memberships as $organizationId) {
            if ($this->roles->of($account, $organizationId) !== []) {
                return $organizationId;
            }
        }

        return null;
    }
}
