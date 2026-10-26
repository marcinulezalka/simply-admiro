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

namespace Simplysmart\Admiro\Panel\Onboarding;

/**
 * Instrukcja kroku onboardingu „Instalacja systemu i konto hero” (kreator `onboarding.show`): etapy w kolejności, każdy
 * z poleceniami do wpisania w terminalu. Tytuły i opisy etapów są tekstami `admiro::dashboard.onboarding_installation_guide.<etap>.*`,
 * polecenia są takie same w każdym języku. Szczegóły wdrożenia na serwer: docs/wdrozenie.md.
 */
final readonly class InstallationGuide
{
    public const string TEXTS = 'admiro::dashboard.onboarding_installation_guide.';

    /**
     * @return list<array{key: string, commands: list<string>}>
     */
    public function stages(): array
    {
        return [
            ['key' => 'download', 'commands' => ['git clone <adres-repozytorium> <katalog>', 'cd <katalog>', 'composer install', 'npm ci && npm run build']],
            ['key' => 'environment', 'commands' => ['cp .env.example .env', 'php artisan key:generate']],
            ['key' => 'database', 'commands' => ['php artisan migrate', 'php artisan admiro:localization:seed', 'php artisan admiro:organizations:seed', 'php artisan admiro:permissions:sync', 'php artisan admiro:legal:seed']],
            ['key' => 'hero', 'commands' => ['php artisan admiro:account:create <e-mail> "<imię i nazwisko>" --role=hero']],
            ['key' => 'deploy', 'commands' => ['vendor/bin/simplysmart deploy:onboarding --dry-run', 'vendor/bin/simplysmart deploy:onboarding']],
        ];
    }
}
