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

namespace Simplysmart\Mainboard\Contracts;

use Simplysmart\Mainboard\Cookies\ConsentScript;

/**
 * Skrypty opcjonalne rejestrowane przez paczki pod kategorią cookies; strona wstawia je nieaktywne, a skrypt banera
 * uruchamia je dopiero po zgodzie na kategorię.
 */
interface ConsentScripts
{
    public function add(ConsentScript $script): void;

    /**
     * @return list<ConsentScript>
     */
    public function all(): array;
}
