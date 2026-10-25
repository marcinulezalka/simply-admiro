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

namespace Simplysmart\Admiro\Permissions;

use Illuminate\Contracts\Auth\Authenticatable;
use Simplysmart\Admiro\Auth\AccountKey;
use Simplysmart\Admiro\Contracts\CurrentRole;

/**
 * Aktywna rola konta w żądaniu (wiązanie scoped): pamięta konto (klasa modelu i identyfikator), organizację i rolę,
 * więc sprawdzenia uprawnień innych kont albo w innej organizacji nie są zawężane.
 */
final class RequestRole implements CurrentRole
{
    /**
     * @var array{account: class-string, key: int|string, organization: int, role: string}|null
     */
    private ?array $active = null;

    public function of(Authenticatable $account, ?int $organizationId): ?string
    {
        $active = $this->active;

        if ($active === null || $active['account'] !== $account::class || $active['organization'] !== $organizationId) {
            return null;
        }

        return AccountKey::from($account->getAuthIdentifier()) === $active['key'] ? $active['role'] : null;
    }

    public function set(Authenticatable $account, int $organizationId, string $role): void
    {
        $this->active = ['account' => $account::class, 'key' => AccountKey::of($account), 'organization' => $organizationId, 'role' => $role];
    }

    public function clear(): void
    {
        $this->active = null;
    }
}
