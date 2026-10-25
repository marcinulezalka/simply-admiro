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
use Illuminate\Config\Repository;
use Illuminate\Http\Request;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * W strefie z `security.block_ai_agents` agenci AI (`mainboard.security.ai_agents`, fragmenty User-Agent)
 * dostają 403 przed uruchomieniem sesji, niezależnie od nagłówka X-Robots-Tag.
 */
final readonly class BlockAiAgents
{
    public function __construct(
        private CurrentZone $zone,
        private Repository $config,
    ) {}

    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->zone->get()->security->blockAiAgents && $this->isAiAgent((string) $request->userAgent())) {
            throw new AccessDeniedHttpException();
        }

        return $next($request);
    }

    private function isAiAgent(string $userAgent): bool
    {
        foreach ($this->config->array('mainboard.security.ai_agents') as $agent) {
            if (is_string($agent) && $agent !== '' && stripos($userAgent, $agent) !== false) {
                return true;
            }
        }

        return false;
    }
}
