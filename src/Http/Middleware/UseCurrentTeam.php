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

namespace Simplysmart\Admiro\Http\Middleware;

use Simplysmart\Admiro\Contracts\HasZoneRoles;
use Simplysmart\Admiro\Contracts\CurrentAccount;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\UrlGenerator;
use Simplysmart\Admiro\Contracts\CurrentOrganization;
use Simplysmart\Admiro\Contracts\CurrentRole;
use Simplysmart\Admiro\Contracts\CurrentZone;
use Simplysmart\Admiro\Models\ZoneAccount;
use Simplysmart\Admiro\Permissions\OrganizationSelector;
use Simplysmart\Admiro\Permissions\RoleSelector;
use Simplysmart\Admiro\Zones\ContextKeys;
use Simplysmart\Admiro\Zones\ZoneDefinition;
use Simplysmart\Admiro\Zones\ZoneRedirects;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ustawia organizację żądania (OrganizationSelector) i w strefie z `role_contexts` aktywną rolę konta w niej
 * (RoleSelector) dla sprawdzeń uprawnień i wiązań tras, a po odpowiedzi je czyści, więc nie przechodzą do kolejnego
 * żądania (Octane, testy). Stoi po StartSession, przed SubstituteBindings. W strefie z `context_in_url` kontekst
 * wybiera klucz w adresie (Zones\ContextKeys, parametr trasy usuwany przed kontrolerem): rozpoznany trafia do sesji
 * (strony bez klucza — konto, logowanie — pracują w ostatnim kontekście), a każdy adres generowany w żądaniu dostaje
 * klucz bieżącego kontekstu (URL::defaults); cudzy albo nieznany klucz przekierowuje na pulpit bieżącego kontekstu,
 * klucz z poprzedniego `APP_KEY` — na ten sam adres z aktualnym kluczem.
 */
final readonly class UseCurrentTeam
{
    public function __construct(
        private CurrentZone $zone,
        private CurrentAccount $account,
        private OrganizationSelector $organizations,
        private CurrentOrganization $organization,
        private RoleSelector $roles,
        private CurrentRole $role,
        private ContextKeys $keys,
        private UrlGenerator $url,
    ) {}

    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $zone = $this->zone->get();
        $account = $this->account->get();
        $account = $account instanceof Model ? $account : null;
        $route = $request->route();
        $routeKey = $zone->contextInUrl ? $route->parameter(ContextKeys::PARAMETER) : null;
        $match = is_string($routeKey) && $account instanceof ZoneAccount ? $this->keys->resolve($zone, $account, $routeKey) : null;

        if ($match !== null) {
            $this->remember($zone, $match->organizationId, $match->role, $request);
        }

        $organizationId = $this->organizations->select($zone, $account, $request->session());
        $this->organization->set($organizationId);
        $this->role->clear();
        $role = null;

        if ($account instanceof ZoneAccount && $organizationId !== null) {
            $role = $this->roles->select($zone, $account, $organizationId, $request->session());

            if ($role !== null) {
                $this->role->set($account, $organizationId, $role);
            }
        }

        if ($account instanceof HasZoneRoles) {
            $account->forgetZonePermissions();
        }

        if ($zone->contextInUrl && $account instanceof ZoneAccount) {
            $key = $this->keys->key($zone, $account, $organizationId, $role);
            $this->url->defaults([ContextKeys::PARAMETER => $key]);

            if (is_string($routeKey) && $routeKey !== $key) {
                $this->organization->set(null);
                $this->role->clear();

                return $this->redirectTo($zone, $route, $request, $match !== null && ! $match->current);
            }
        }

        $route->forgetParameter(ContextKeys::PARAMETER);

        try {
            return $next($request);
        } finally {
            $this->organization->set(null);
            $this->role->clear();
        }
    }

    /**
     * Zapisuje w sesji strefy kontekst rozpoznany z klucza w adresie.
     */
    private function remember(ZoneDefinition $zone, ?int $organizationId, ?string $role, Request $request): void
    {
        if ($organizationId === null) {
            return;
        }

        if ($zone->teams) {
            $this->organizations->remember($organizationId, $request->session());
        }

        if ($role !== null) {
            $this->roles->remember($organizationId, $role, $request->session());
        }
    }

    /**
     * Przekierowanie z adresu z cudzym, nieznanym albo przestarzałym kluczem: przestarzały (stale) w żądaniu GET — ten
     * sam adres z kluczem bieżącego kontekstu, inaczej pulpit strefy.
     */
    private function redirectTo(ZoneDefinition $zone, Route $route, Request $request, bool $stale): RedirectResponse
    {
        $name = $route->getName();
        $target = $stale && $request->isMethod('GET') && $name !== null
            ? $this->url->route($name, [...$route->parameters(), ...$this->url->getDefaultParameters()])
            : $this->url->route($zone->routeName(ZoneRedirects::HOME_ROUTE));

        return new RedirectResponse($target);
    }
}
