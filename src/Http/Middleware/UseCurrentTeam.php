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
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Permissions\OrganizationSelector;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ustawia organizację żądania (OrganizationSelector) dla sprawdzeń uprawnień i wiązań tras, a po odpowiedzi
 * ją czyści, więc nie przechodzi do kolejnego żądania (Octane, testy). Stoi po StartSession, przed SubstituteBindings.
 */
final readonly class UseCurrentTeam
{
    public function __construct(
        private CurrentZone $zone,
        private CurrentAccount $account,
        private OrganizationSelector $selector,
        private CurrentOrganization $organization,
    ) {}

    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $zone = $this->zone->get();
        $account = $this->account->get();
        $account = $account instanceof Model ? $account : null;

        $this->organization->set($this->selector->select($zone, $account, $request->session()));
        if ($account instanceof HasZoneRoles) {
            $account->forgetZonePermissions();
        }

        try {
            return $next($request);
        } finally {
            $this->organization->set(null);
        }
    }
}
