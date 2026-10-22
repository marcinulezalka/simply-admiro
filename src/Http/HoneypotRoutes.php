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

namespace Simplysmart\Mainboard\Http;

use Illuminate\Config\Repository;
use Illuminate\Routing\Router;
use Simplysmart\Mainboard\Http\Controllers\HoneypotController;

/**
 * Trasy pułapek `mainboard.honeypot.<n>.show|trap` pod `mainboard.honeypot.paths` (bez prefiksu języka,
 * bez sesji, z limitem `mainboard.honeypot`). Rejestrowane po wszystkich providerach, więc prawdziwa trasa
 * o tym samym adresie zawsze wygrywa.
 */
final readonly class HoneypotRoutes
{
    public const string THROTTLE = 'mainboard.honeypot';

    private const string ANY = 'any';

    public function __construct(
        private Router $router,
        private Repository $config,
    ) {}

    public function register(): void
    {
        $this->router->middleware('throttle:' . self::THROTTLE)->name('mainboard.honeypot.')->group(function (): void {
            foreach (array_values($this->config->array('mainboard.honeypot.paths')) as $index => $path) {
                if (! is_string($path)) {
                    continue;
                }

                $this->router->get($path, [HoneypotController::class, 'show'])->where(self::ANY, '.*')->name("{$index}.show");
                $this->router->post($path, [HoneypotController::class, 'trap'])->where(self::ANY, '.*')->name("{$index}.trap");
            }
        });
    }
}
