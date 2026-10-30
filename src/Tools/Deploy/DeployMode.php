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

namespace Simplysmart\Admiro\Tools\Deploy;

/**
 * Rodzaj wdrożenia: zwykłe, z onboardingiem (wdrożenie + wartości pierwszej instalacji) albo od zera
 * (onboarding na pustej bazie: `migrate:fresh` usuwa wszystkie tabele i dane na serwerze).
 */
enum DeployMode
{
    case Deploy;
    case Onboarding;
    case Fresh;

    public function commandName(): string
    {
        return match ($this) {
            self::Deploy => 'deploy',
            self::Onboarding => 'deploy:onboarding',
            self::Fresh => 'deploy:fresh',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Deploy => 'Wdrożenie: z <projekt> przed-produkcja, pytanie i produkcja (deploy-vendors); bez — serwer z deploy.env',
            self::Onboarding => 'Wdrożenie i pierwsza instalacja: konta z pliku onboardingu (istniejące są pomijane)',
            self::Fresh => 'Wdrożenie od zera: migrate:fresh (usuwa wszystkie dane, z <projekt> także przed-produkcji), seedery i konta',
        };
    }

    /**
     * Czy wdrożenie zakłada konta z pliku onboardingu.
     */
    public function createsAccounts(): bool
    {
        return $this !== self::Deploy;
    }

    /**
     * Czy wdrożenie czyści bazę na serwerze (`migrate:fresh`) i dlatego wymaga potwierdzenia.
     */
    public function resetsDatabase(): bool
    {
        return $this === self::Fresh;
    }
}
