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

namespace Simplysmart\Mainboard\Zones;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use LogicException;
use Simplysmart\Mainboard\Auth\ZoneAuthSettings;
use Simplysmart\Mainboard\Contracts\CodeAndPinAccount;

/**
 * Niezmienna definicja jednej strefy (budowana przez ZoneDefinitionFactory z configu paczki strefy).
 */
final readonly class ZoneDefinition
{
    /**
     * @param string $name Nazwa strefy (klucze cache, nazwa ciasteczka, grupa middleware).
     * @param string $routeNamePrefix Stały prefiks nazw tras (bez kropki), niezależny od configu.
     * @param string $slug Segment URL strefy; '' = bez segmentu.
     * @param string $guard Guard strefy.
     * @param string $provider Provider użytkowników guardu.
     * @param class-string<Authenticatable>|null $model Model kont strefy; null = strefa bez logowania.
     * @param string $routesPath Katalog plików tras strefy (public.php, account.php, zone.php).
     * @param ZoneAuthSettings|null $authSettings Ustawienia logowania; null = strefa bez logowania.
     */
    public function __construct(
        public string $name,
        public string $routeNamePrefix,
        public string $slug,
        public string $guard,
        public string $provider,
        public ?string $model,
        public string $routesPath,
        public ZoneSessionSettings $session,
        public ?ZoneAuthSettings $authSettings = null,
    ) {}

    /**
     * Czy strefa ma konta i logowanie.
     */
    public function requiresLogin(): bool
    {
        return $this->model !== null;
    }

    /**
     * Ustawienia logowania strefy.
     *
     * @throws LogicException Gdy strefa nie ma logowania.
     */
    public function auth(): ZoneAuthSettings
    {
        return $this->authSettings ?? throw new LogicException("Zone [{$this->name}] has no sign-in.");
    }

    /**
     * Model kont strefy logującej się kodem i PIN-em.
     *
     * @return class-string<Model&CodeAndPinAccount>
     *
     * @throws LogicException Gdy strefa nie ma logowania albo model nie jest takim modelem Eloquent.
     */
    public function accountModel(): string
    {
        $model = $this->model;

        if ($model === null || ! is_a($model, Model::class, true) || ! is_a($model, CodeAndPinAccount::class, true)) {
            throw new LogicException("Zone [{$this->name}] model must be an Eloquent model implementing " . CodeAndPinAccount::class . '.');
        }

        return $model;
    }

    /**
     * Pełna nazwa trasy strefy, np. "alpha.dashboard".
     */
    public function routeName(string $name): string
    {
        return $this->routeNamePrefix . '.' . $name;
    }
}
