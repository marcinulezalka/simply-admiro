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

use Closure;
use Illuminate\Http\Request;
use Simplysmart\Admiro\Auth\SignIn\PendingSignInResolver;
use Simplysmart\Admiro\Auth\SignIn\SignInFlow;
use Simplysmart\Admiro\Auth\SignIn\SignInFlowState;
use Simplysmart\Admiro\Contracts\CurrentZone;
use Symfony\Component\HttpFoundation\Response;

/**
 * Wpuszcza na krok logowania (`EnsureSignInStep:<krok>`) tylko wtedy, gdy to następny wymagany
 * krok i nie minął jego czas; inaczej logowanie zaczyna się od nowa (kroków nie da się przeskoczyć).
 */
final readonly class EnsureSignInStep
{
    public function __construct(
        private SignInFlowState $state,
        private SignInFlow $flow,
        private PendingSignInResolver $pending,
        private CurrentZone $zone,
    ) {}

    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next, string $step): Response
    {
        if (! $this->state->isStarted()) {
            $this->pending->abort(text('sign_in.expired'));
        }

        $current = $this->flow->next($this->zone->get()->auth(), $this->pending->resolve());

        if ($current === null || $current->key() !== $step) {
            $this->pending->abort(text('sign_in.expired'));
        }

        $this->state->enter($step);

        if ($this->state->hasStepExpired($current->ttlMinutes())) {
            $this->pending->abort(text('sign_in.expired'));
        }

        return $next($request);
    }
}
