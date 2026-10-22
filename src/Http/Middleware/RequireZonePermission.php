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
use Illuminate\Http\Request;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Contracts\HasZoneRoles;
use Simplysmart\Mainboard\Contracts\ZonePermissionChecker;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Trasa wymagająca uprawnienia (`zone.permission:<uprawnienie>`): konto z guardu bieżącej strefy, w bieżącej
 * organizacji, z odmowami (ZonePermissionChecker). Brak uprawnienia to 403. Pliki tras strefy nie znają
 * guardu, więc używają Zones\ZonePermission::middleware().
 */
final readonly class RequireZonePermission
{
    public const string ALIAS = 'zone.permission';

    public function __construct(
        private CurrentZone $zone,
        private AuthFactory $auth,
        private ZonePermissionChecker $permissions,
    ) {}

    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $zone = $this->zone->get();
        $account = $zone->requiresLogin() ? $this->auth->guard($zone->guard)->user() : null;

        if (! $account instanceof HasZoneRoles || ! $this->permissions->allows($zone, $account, $permission)) {
            throw new AccessDeniedHttpException();
        }

        return $next($request);
    }
}
