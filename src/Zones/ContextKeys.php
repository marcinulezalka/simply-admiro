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

namespace Simplysmart\Admiro\Zones;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Session\Session;
use Simplysmart\Admiro\Models\Organization;
use Simplysmart\Admiro\Models\ZoneAccount;
use Simplysmart\Admiro\Permissions\OrganizationSelector;
use Simplysmart\Admiro\Permissions\RoleSelector;
use Simplysmart\Admiro\Queries\Organizations\ActiveMemberships;
use Simplysmart\Admiro\Queries\Permissions\ZoneRoleNames;

/**
 * Klucz kontekstu konta w adresie strefy z `context_in_url` (`/<slug>/<klucz>/...`): nieprzezroczysty skrót HMAC
 * strefy, konta, organizacji i roli — bez nazw organizacji i ról w adresie, inny dla każdego konta (cudzy klucz nic
 * nie mówi). Sekret to klucz pochodny `APP_KEY` z etykietą PURPOSE (niezależny od szyfrowania i podpisanych adresów
 * Laravela), więc zmiana `APP_KEY` zmienia wszystkie klucze; klucze z `APP_PREVIOUS_KEYS` są rozpoznawane jako
 * przestarzałe. Kontekstami konta są pary organizacja–rola (strefa bez `teams`: organizacja operatora; organizacja
 * bez roli konta i konto bez organizacji też mają klucz), te same, które wybierają OrganizationSelector i RoleSelector.
 */
final readonly class ContextKeys
{
    /** Parametr trasy z kluczem kontekstu. */
    public const string PARAMETER = 'context';

    /** Wzorzec klucza w trasie (szesnastkowo, LENGTH znaków). */
    public const string PATTERN = '[0-9a-f]{10}';

    private const int LENGTH = 10;

    /** Etykieta klucza pochodnego `APP_KEY`. */
    private const string PURPOSE = 'zone-context';

    public function __construct(
        private ActiveMemberships $memberships,
        private ZoneRoleNames $roles,
        private OrganizationSelector $organizations,
        private RoleSelector $roleSelector,
        private Repository $config,
    ) {}

    /**
     * Klucz kontekstu konta: organizacja i rola (null — bez organizacji albo bez roli).
     */
    public function key(ZoneDefinition $zone, ZoneAccount $account, ?int $organizationId, ?string $role): string
    {
        return $this->sign($this->secrets()[0], $zone, $account, $organizationId, $role);
    }

    /**
     * Klucz kontekstu zapamiętanego w sesji strefy (domyślny po zalogowaniu: najszersza rola — RoleSelector).
     */
    public function current(ZoneDefinition $zone, ZoneAccount $account, Session $session): string
    {
        $organizationId = $this->organizations->select($zone, $account, $session);
        $role = $organizationId !== null ? $this->roleSelector->select($zone, $account, $organizationId, $session) : null;

        return $this->key($zone, $account, $organizationId, $role);
    }

    /**
     * Kontekst konta o tym kluczu albo null (klucz cudzy, nieznany albo kontekst, którego konto już nie ma).
     */
    public function resolve(ZoneDefinition $zone, ZoneAccount $account, string $key): ?ContextMatch
    {
        $contexts = $this->contexts($zone, $account);

        foreach ($this->secrets() as $index => $secret) {
            foreach ($contexts as [$organizationId, $role]) {
                if (hash_equals($this->sign($secret, $zone, $account, $organizationId, $role), $key)) {
                    return new ContextMatch($organizationId, $role, $index === 0);
                }
            }
        }

        return null;
    }

    /**
     * Pary organizacja–rola konta; organizacja bez roli konta jako para z rolą null, konto bez organizacji jako para
     * (null, null).
     *
     * @return list<array{0: ?int, 1: ?string}>
     */
    private function contexts(ZoneDefinition $zone, ZoneAccount $account): array
    {
        $contexts = [];

        foreach ($zone->teams ? $this->memberships->ids($account) : [Organization::OPERATOR_ID] as $organizationId) {
            $roles = $zone->roleContexts ? $this->roles->of($account, $organizationId) : [];

            foreach ($roles === [] ? [null] : $roles as $role) {
                $contexts[] = [$organizationId, $role];
            }
        }

        return $contexts === [] ? [[null, null]] : $contexts;
    }

    private function sign(string $secret, ZoneDefinition $zone, ZoneAccount $account, ?int $organizationId, ?string $role): string
    {
        $key = $account->getKey();
        $payload = implode('|', [$zone->name, is_scalar($key) ? (string) $key : '', (string) ($organizationId ?? 0), $role ?? '']);

        return substr(hash_hmac('sha256', $payload, $secret), 0, self::LENGTH);
    }

    /**
     * Sekrety kluczy: z bieżącego `APP_KEY`, potem z `APP_PREVIOUS_KEYS`.
     *
     * @return non-empty-list<string>
     */
    private function secrets(): array
    {
        $previous = $this->config->get('app.previous_keys', []);
        $keys = [$this->config->get('app.key'), ...(is_array($previous) ? $previous : [])];

        $keys = array_values(array_filter($keys, static fn(mixed $key): bool => is_string($key) && $key !== ''));

        return array_map(static fn(string $key): string => hash_hmac('sha256', self::PURPOSE, $key, true), $keys) ?: [hash_hmac('sha256', self::PURPOSE, '', true)];
    }
}
