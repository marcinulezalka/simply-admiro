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

namespace Simplysmart\Admiro\Http;

use Simplysmart\Mainboard\Contracts\CurrentAccount;
use Simplysmart\Admiro\Enums\Permission;
use Simplysmart\Admiro\Models\AdmiroAccount;
use Simplysmart\Mainboard\Auth\AccountActor;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Contracts\ZonePermissionChecker;
use Simplysmart\Mainboard\Zones\ZoneDefinition;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Zalogowany administrator żądania: konto z guardu strefy admiro, jego uprawnienia (w organizacji operatora,
 * ustawionej przez Core) i aktor do dziennika akcji.
 */
final readonly class AdminContext
{
    public function __construct(
        private CurrentZone $zone,
        private CurrentAccount $account,
        private ZonePermissionChecker $permissions,
    ) {}

    public function zone(): ZoneDefinition
    {
        return $this->zone->get();
    }

    /**
     * @throws AccessDeniedHttpException Gdy żądanie nie ma zalogowanego administratora.
     */
    public function admin(): AdmiroAccount
    {
        $account = $this->account->get();

        return $account instanceof AdmiroAccount ? $account : throw new AccessDeniedHttpException();
    }

    public function can(Permission $permission): bool
    {
        return $this->permissions->allows($this->zone(), $this->admin(), $permission);
    }

    public function actor(): AccountActor
    {
        return AccountActor::of($this->zone(), $this->admin());
    }
}
