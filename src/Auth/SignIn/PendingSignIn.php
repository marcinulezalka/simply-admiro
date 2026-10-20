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

namespace Simplysmart\Mainboard\Auth\SignIn;

use Illuminate\Database\Eloquent\Model;
use Simplysmart\Mainboard\Contracts\CodeAndPinAccount;

/**
 * Trwające logowanie: podany adres, konto (null = brak konta albo konto, które nie może się
 * zalogować, czyli decoy) i wykonane kroki.
 */
final readonly class PendingSignIn
{
    /**
     * @param list<string> $completed
     */
    public function __construct(
        public string $email,
        public (Model&CodeAndPinAccount)|null $account,
        public array $completed,
    ) {}

    public function hasCompleted(string $step): bool
    {
        return in_array($step, $this->completed, true);
    }
}
