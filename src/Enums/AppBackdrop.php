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

namespace Simplysmart\Admiro\Enums;

/**
 * Tło płótna layoutu aplikacji (`backdrop` w configu strefy, x-admiro::app.backdrop): Brand — duży logotyp simplysmart
 * na środku okna; ZoneLogo — logo strefy (`logo`) wtopione w płótno tak samo, przy dolnej krawędzi okna i lekko
 * przycięte od dołu.
 */
enum AppBackdrop: string
{
    case Brand = 'brand';
    case ZoneLogo = 'zone-logo';
}
