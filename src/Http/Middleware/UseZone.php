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
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Contracts\ZoneRegistry;
use Simplysmart\Mainboard\Zones\ZoneSessionConfigurator;
use Symfony\Component\HttpFoundation\Response;

/**
 * Wprowadza żądanie w strefę (`UseZone:<strefa>`): ustawia CurrentZone i sesję strefy
 * (ciasteczko, tabela, czas życia), a po odpowiedzi przywraca stan aplikacji.
 *
 * Musi działać przed StartSession. Domyślny guard zmienia tylko po to, żeby sterownik sesji
 * `database` zapisał `user_id` konta strefy; kod Core i stref zawsze nazywa guard jawnie.
 */
final readonly class UseZone
{
    public function __construct(
        private ZoneRegistry $zones,
        private CurrentZone $current,
        private ZoneSessionConfigurator $session,
        private AuthManager $auth,
    ) {}

    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next, string $zone): Response
    {
        $definition = $this->zones->get($zone);
        $previousGuard = $this->auth->getDefaultDriver();

        $this->current->set($definition);
        $this->session->apply($definition);

        if ($definition->requiresLogin()) {
            $this->auth->shouldUse($definition->guard);
        }

        try {
            return $next($request);
        } finally {
            $this->auth->shouldUse($previousGuard);
            $this->session->restore();
            $this->current->forget();
        }
    }
}
