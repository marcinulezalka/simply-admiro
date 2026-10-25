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
 * Konto, którego wszystkie sesje można unieważnić podbiciem epoki.
 *
 * Epoka trafia do sesji przy logowaniu; sesja z inną epoką niż konto zostaje wylogowana.
 */
interface RevocableAccess
{
    public function authEpoch(): int;
}
