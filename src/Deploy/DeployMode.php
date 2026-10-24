<?php

declare(strict_types=1);

/**
 * This file is part of simplysmart/tools.
 *
 * Copyright (c) 2026 Marcin Ulezalka <marcin.ulezalka@simplysmart.pl>
 *
 * Licensed under the Simplysmart Restricted Source License 1.0.
 * See the LICENSE file distributed with this source code.
 */

namespace Simplysmart\Tools\Deploy;

/**
 * Rodzaj wdrożenia: zwykłe albo z onboardingiem (wdrożenie + wartości pierwszej instalacji).
 */
enum DeployMode
{
    case Deploy;
    case Onboarding;

    public function commandName(): string
    {
        return match ($this) {
            self::Deploy => 'deploy',
            self::Onboarding => 'deploy:onboarding',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Deploy => 'Wdrożenie na serwer: sprawdzenie repozytoriów, manifest wdrożeniowy, scp i skrypt serwerowy przez ssh',
            self::Onboarding => 'Wdrożenie i pierwsza instalacja: konta z infrastructure/deploy.onboarding.env (istniejące są pomijane)',
        };
    }
}
