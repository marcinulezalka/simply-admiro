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

namespace Simplysmart\Admiro\Auth\SignIn;

use Illuminate\Database\Eloquent\Model;
use Simplysmart\Admiro\Contracts\CodeAndPinAccount;

/**
 * Trwające logowanie: podany adres, konto (null = brak konta albo konto, które nie może się
 * zalogować, czyli decoy), wykonane kroki, rozpoznane zaufane urządzenie i prośba o jego zapamiętanie.
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
        public bool $deviceTrusted = false,
        public bool $rememberDevice = false,
    ) {}

    /**
     * Czy po zalogowaniu zapamiętać urządzenie: tylko na prośbę z kroku kodu z e-maila, nigdy na ścieżce
     * zaufanego urządzenia (ono nie może dopisać kolejnego).
     */
    public function wantsDeviceRemembered(string $codeStep): bool
    {
        return $this->account !== null && $this->rememberDevice && ! $this->deviceTrusted && $this->hasCompleted($codeStep);
    }

    public function hasCompleted(string $step): bool
    {
        return in_array($step, $this->completed, true);
    }
}
