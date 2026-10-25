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

/**
 * Indeks wyszukiwania po polu szyfrowanym (RODO): deterministyczny skrót z kluczem aplikacji.
 */
interface BlindIndex
{
    public function hash(string $value): string;
}
