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

namespace Simplysmart\Admiro\Contracts;

/**
 * Opcjonalne rozszerzenie Contracts\ZoneNavigation: postęp przeglądu widoków strefy. Layout stref
 * (x-admiro::app.zone-layout) rysuje na środku treści bieżącej strony napis „TEMP”, gdy jej widok nie jest jeszcze
 * gotowy, żeby od razu było widać, co zostało do zrobienia.
 */
interface ZonePageProgress
{
    /**
     * Czy widok bieżącej strony jest jeszcze w przygotowaniu (napis „TEMP” nad treścią).
     */
    public function pageInProgress(): bool;
}
