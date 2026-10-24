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

namespace Simplysmart\Mainboard\Http\Middleware;

use Simplysmart\Mainboard\Contracts\HasZoneRoles;
use Simplysmart\Mainboard\Contracts\CurrentAccount;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Simplysmart\Mainboard\Contracts\CurrentOrganization;
use Simplysmart\Mainboard\Contracts\CurrentRole;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Models\ZoneAccount;
use Simplysmart\Mainboard\Permissions\OrganizationSelector;
use Simplysmart\Mainboard\Permissions\RoleSelector;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ustawia organizację żądania (OrganizationSelector) i w strefie z `role_contexts` aktywną rolę konta w niej
 * (RoleSelector) dla sprawdzeń uprawnień i wiązań tras, a po odpowiedzi je czyści, więc nie przechodzą do kolejnego
 * żądania (Octane, testy). Stoi po StartSession, przed SubstituteBindings.
 */
final readonly class UseCurrentTeam
{
    public function __construct(
        private CurrentZone $zone,
        private CurrentAccount $account,
        private OrganizationSelector $selector,
        private CurrentOrganization $organization,
        private RoleSelector $roles,
        private CurrentRole $role,
    ) {}

    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $zone = $this->zone->get();
        $account = $this->account->get();
        $account = $account instanceof Model ? $account : null;

        $organizationId = $this->selector->select($zone, $account, $request->session());
        $this->organization->set($organizationId);
        $this->role->clear();

        if ($account instanceof ZoneAccount && $organizationId !== null) {
            $role = $this->roles->select($zone, $account, $organizationId, $request->session());

            if ($role !== null) {
                $this->role->set($account, $organizationId, $role);
            }
        }

        if ($account instanceof HasZoneRoles) {
            $account->forgetZonePermissions();
        }

        try {
            return $next($request);
        } finally {
            $this->organization->set(null);
            $this->role->clear();
        }
    }
}
