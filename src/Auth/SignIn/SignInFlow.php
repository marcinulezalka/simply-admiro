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

namespace Simplysmart\Mainboard\Auth\SignIn;

use Illuminate\Contracts\Container\Container;
use Simplysmart\Mainboard\Auth\ZoneAuthSettings;
use Simplysmart\Mainboard\Contracts\SignInStep;

/**
 * Kroki logowania strefy w kolejności z configu; następny krok to pierwszy wymagany i niewykonany.
 */
final readonly class SignInFlow
{
    public function __construct(
        private Container $container,
    ) {}

    /**
     * @return list<SignInStep>
     */
    public function steps(ZoneAuthSettings $settings): array
    {
        return array_map(fn(string $step): SignInStep => $this->container->make($step), $settings->steps);
    }

    public function step(ZoneAuthSettings $settings, string $key): ?SignInStep
    {
        foreach ($this->steps($settings) as $step) {
            if ($step->key() === $key) {
                return $step;
            }
        }

        return null;
    }

    public function next(ZoneAuthSettings $settings, PendingSignIn $signIn): ?SignInStep
    {
        foreach ($this->steps($settings) as $step) {
            if (! $signIn->hasCompleted($step->key()) && $step->isRequired($signIn)) {
                return $step;
            }
        }

        return null;
    }
}
