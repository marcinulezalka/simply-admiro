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

namespace Simplysmart\Admiro\Contracts;

use Simplysmart\Admiro\Models\Organization;
use Simplysmart\Admiro\Permissions\OwnerData;

/**
 * Zakłada właściciela w strefie (konto w tabeli tej strefy z rolą `owner`). Strefa administracyjna woła ten
 * kontrakt przez rejestr Permissions\ZoneOwnerProvisioners, więc nie zna klas innych stref ani nie
 * współdzieli z nimi kont.
 */
interface ZoneOwnerProvisioner
{
    /**
     * Nazwa strefy, w której powstaje właściciel.
     */
    public function zone(): string;

    /**
     * Konto właściciela (istniejące konto o tym adresie albo nowe, bez PIN-u) z rolą `owner` w organizacji;
     * null = organizacja operatora (strefa bez zespołów). Zwraca token konta.
     */
    public function provisionOwner(OwnerData $owner, ?Organization $organization = null): string;
}
