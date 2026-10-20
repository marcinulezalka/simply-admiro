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
 * Pełne konto strefy logującej się kodem z e-maila i PIN-em; model strefy implementuje je
 * traitem Auth\Concerns\SignsInWithCodeAndPin. Serwisy wymagają tylko części, której używają.
 *
 * @see \Simplysmart\Mainboard\Models\ZoneAccount
 */
interface CodeAndPinAccount extends SignInAccount, HasPinCredential, LockableAccount, RevocableAccess {}
