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

namespace Simplysmart\Mainboard\Permissions;

use Illuminate\Contracts\Session\Session;
use Simplysmart\Mainboard\Models\ZoneAccount;
use Simplysmart\Mainboard\Queries\Permissions\ZoneRoleNames;
use Simplysmart\Mainboard\Zones\ZoneDefinition;

/**
 * Wybiera aktywną rolę (kontekst) konta w organizacji strefy z `role_contexts`: rolę wybraną przez konto (sesja strefy,
 * osobno dla każdej organizacji), o ile konto nadal ją ma, inaczej pierwszą rolę konta w organizacji — zapamiętaną w
 * sesji, więc nowa rola nadana w trakcie sesji nie zmienia kontekstu sama; bez ról albo poza taką strefą: brak kontekstu.
 */
final readonly class RoleSelector
{
    public const string SESSION_KEY = 'mainboard.role';

    public function __construct(
        private ZoneRoleNames $roles,
    ) {}

    public function select(ZoneDefinition $zone, ZoneAccount $account, ?int $organizationId, Session $session): ?string
    {
        if (! $zone->roleContexts || $organizationId === null) {
            return null;
        }

        $roles = $this->roles->of($account, $organizationId);
        $chosen = $session->get($this->key($organizationId));

        if (is_string($chosen) && in_array($chosen, $roles, true)) {
            return $chosen;
        }

        $role = $roles[0] ?? null;

        if ($role !== null) {
            $session->put($this->key($organizationId), $role);
        }

        return $role;
    }

    /**
     * Role konta, między którymi może się przełączać (co najmniej dwie), albo lista pusta.
     *
     * @return list<string>
     */
    public function choices(ZoneDefinition $zone, ZoneAccount $account, ?int $organizationId): array
    {
        if (! $zone->roleContexts || $organizationId === null) {
            return [];
        }

        $roles = $this->roles->of($account, $organizationId);

        return count($roles) > 1 ? $roles : [];
    }

    /**
     * Zapamiętuje wybór konta w sesji strefy; rola, której konto nie ma w organizacji, jest odrzucana.
     */
    public function choose(ZoneDefinition $zone, ZoneAccount $account, int $organizationId, string $role, Session $session): bool
    {
        if (! in_array($role, $this->choices($zone, $account, $organizationId), true)) {
            return false;
        }

        $session->put($this->key($organizationId), $role);

        return true;
    }

    private function key(int $organizationId): string
    {
        return self::SESSION_KEY . '.' . $organizationId;
    }
}
