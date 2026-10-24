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

namespace Simplysmart\Tools\Process;

use RuntimeException;

/**
 * Proces nie dał się uruchomić; komunikat trafia bezpośrednio na STDERR.
 */
final class ProcessException extends RuntimeException {}
