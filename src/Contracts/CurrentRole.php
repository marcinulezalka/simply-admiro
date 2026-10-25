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

use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Aktywna rola (kontekst) konta zalogowanego w bieżącym żądaniu, w strefie z `role_contexts`: uprawnienia tego konta
 * w tej organizacji są zawężone do niej. Ustawia ją middleware UseCurrentTeam (Permissions\RoleSelector); inne konta
 * i inne organizacje nie mają aktywnej roli (uprawnienia z sumy ról).
 */
interface CurrentRole
{
    /**
     * Aktywna rola konta w organizacji albo null (brak kontekstu: wszystkie role konta).
     */
    public function of(Authenticatable $account, ?int $organizationId): ?string;

    public function set(Authenticatable $account, int $organizationId, string $role): void;

    public function clear(): void;
}
