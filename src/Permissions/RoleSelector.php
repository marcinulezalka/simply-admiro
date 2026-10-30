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
use Simplysmart\Admiro\Models\ZoneAccount;
use Simplysmart\Admiro\Queries\Permissions\ZoneRoleNames;
use Simplysmart\Admiro\Zones\ZoneDefinition;

/**
 * Wybiera aktywną rolę (kontekst) konta w organizacji strefy z `role_contexts`: rolę wybraną przez konto (sesja strefy,
 * osobno dla każdej organizacji), o ile konto nadal ją ma, inaczej domyślną — najszerszą rolę konta w organizacji
 * (najwięcej uprawnień, ZoneRoleNames::permissionCounts(); przy remisie pierwszą), np. właściciel przed lekarzem —
 * zapamiętaną w sesji, więc nowa rola nadana w trakcie sesji nie zmienia kontekstu sama; bez ról albo poza taką
 * strefą: brak kontekstu.
 */
final readonly class RoleSelector
{
    public const string SESSION_KEY = 'admiro.role';

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

        $role = $this->broadest($zone, $roles);

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

    /**
     * Zapamiętuje w sesji strefy rolę rozpoznaną z klucza kontekstu w adresie (Zones\ContextKeys sprawdził już, że
     * konto ma ją w tej organizacji).
     */
    public function remember(int $organizationId, string $role, Session $session): void
    {
        $session->put($this->key($organizationId), $role);
    }

    /**
     * Rola z największą liczbą uprawnień (przy remisie pierwsza); null przy pustej liście.
     *
     * @param list<string> $roles
     */
    private function broadest(ZoneDefinition $zone, array $roles): ?string
    {
        if (count($roles) < 2) {
            return $roles[0] ?? null;
        }

        $counts = $this->roles->permissionCounts($zone);
        $broadest = $roles[0];

        foreach ($roles as $role) {
            if (($counts[$role] ?? 0) > ($counts[$broadest] ?? 0)) {
                $broadest = $role;
            }
        }

        return $broadest;
    }

    private function key(int $organizationId): string
    {
        return self::SESSION_KEY . '.' . $organizationId;
    }
}
