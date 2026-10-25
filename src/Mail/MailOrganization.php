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

namespace Simplysmart\Mainboard\Mail;

use Simplysmart\Mainboard\Contracts\SignInAccount;
use Simplysmart\Mainboard\Database\ColumnNames;
use Simplysmart\Mainboard\Models\Organization;
use Simplysmart\Mainboard\Models\ZoneAccount;
use Simplysmart\Mainboard\Zones\ZoneDefinition;

/**
 * Organizacja, w której imieniu idzie e-mail do konta (jej logo w layoucie): strefa bez organizacji klientów —
 * organizacja operatora; strefa z organizacjami — jedyna aktywna organizacja konta; kilka albo żadna — brak
 * (wtedy logo strefy).
 */
final readonly class MailOrganization
{
    public function for(ZoneDefinition $zone, SignInAccount $account): ?int
    {
        if (! $zone->teams) {
            return Organization::OPERATOR_ID;
        }

        if (! $account instanceof ZoneAccount) {
            return null;
        }

        $organizations = $account->organizations()->where(ColumnNames::IS_ACTIVE, true)->limit(2)->pluck(Organization::TABLE . '.' . ColumnNames::ID);

        return $organizations->count() === 1 && is_int($organizations->first()) ? $organizations->first() : null;
    }
}
