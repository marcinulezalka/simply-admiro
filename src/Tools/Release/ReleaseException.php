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

namespace Simplysmart\Admiro\Tools\Release;

use RuntimeException;

/**
 * Wyjątek oznaczający sytuację, w której wydanie nie może zostać wykonane.
 *
 * Komunikat jest przeznaczony dla użytkownika i trafia bezpośrednio na STDERR.
 */
final class ReleaseException extends RuntimeException {}
