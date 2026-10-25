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

namespace Simplysmart\Admiro\Http\Controllers\Auth;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Simplysmart\Admiro\Auth\SignIn\PendingSignInResolver;
use Simplysmart\Admiro\Auth\SignIn\SignInFlow;
use Simplysmart\Admiro\Auth\SignIn\SignInFlowNavigator;
use Simplysmart\Admiro\Auth\SignIn\SignInFlowState;
use Simplysmart\Admiro\Contracts\CurrentZone;
use Simplysmart\Admiro\Contracts\SignInStep;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Wspólny kontroler każdego kroku logowania; klucz kroku przychodzi z `defaults` trasy.
 */
final readonly class SignInStepController
{
    public function __construct(
        private SignInFlow $flow,
        private PendingSignInResolver $pending,
        private CurrentZone $zone,
    ) {}

    public function show(string $step): View
    {
        $instance = $this->step($step);

        return view($instance->view(), [...$instance->viewData($this->pending->resolve()), 'step' => $step]);
    }

    public function store(Request $request, string $step, SignInFlowState $state, SignInFlowNavigator $navigator): RedirectResponse
    {
        $instance = $this->step($step);

        $instance->handle($this->pending->resolve(), $request->validate($instance->rules()), $request);
        $state->complete($step);

        return $navigator->continue($request);
    }

    private function step(string $key): SignInStep
    {
        return $this->flow->step($this->zone->get()->auth(), $key) ?? throw new NotFoundHttpException();
    }
}
