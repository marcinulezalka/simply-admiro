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

use Closure;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
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
        private AuthFactory $auth,
        private OrganizationSelector $selector,
        private CurrentOrganization $organization,
    ) {}

    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $zone = $this->zone->get();
        $account = $zone->requiresLogin() ? $this->auth->guard($zone->guard)->user() : null;
        $account = $account instanceof Model ? $account : null;

        $this->organization->set($this->selector->select($zone, $account, $request->session()));
        $account?->unsetRelation('roles')->unsetRelation('permissions')->unsetRelation('permissionDenials');

        try {
            return $next($request);
        } finally {
            $this->organization->set(null);
        }
    }
}
