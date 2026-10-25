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

namespace Simplysmart\Admiro\Panel\Http;

use Simplysmart\Admiro\Panel\Enums\Permission;
use Simplysmart\Admiro\Models\Organization;
use Simplysmart\Admiro\Models\ZoneAccount;
use Simplysmart\Admiro\Permissions\PermissionCoverage;
use Simplysmart\Admiro\Zones\ZoneDefinition;

/**
 * Czy zalogowany administrator może zmienić konto (akcje na koncie, usunięcie z organizacji): nigdy własne,
 * a konto innego administratora tylko z `users.manage` i wszystkimi jego uprawnieniami (bez eskalacji).
 * Konta innych stref nie podlegają tej regule.
 */
final readonly class AdministratorGuard
{
    public function __construct(
        private AdminContext $context,
        private PermissionCoverage $coverage,
    ) {}

    public function isOwnAccount(ZoneAccount $account): bool
    {
        return $account->is($this->context->admin());
    }

    public function mayChange(ZoneDefinition $zone, ZoneAccount $account): bool
    {
        if ($this->isOwnAccount($account)) {
            return false;
        }

        if ($zone->name !== $this->context->zone()->name) {
            return true;
        }

        return $this->context->can(Permission::UsersManage)
            && $this->coverage->covers($zone, $this->context->admin(), $this->coverage->of($account, Organization::OPERATOR_ID), Organization::OPERATOR_ID);
    }
}
