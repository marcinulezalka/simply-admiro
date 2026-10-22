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

namespace Simplysmart\Mainboard\Permissions;

use JsonException;
use Simplysmart\Mainboard\Contracts\ZoneRegistry;

/**
 * Czyta i waliduje plik ról strefy, zanim cokolwiek trafi do bazy:
 *
 * ```json
 * {"zone": "shop", "permissions": ["orders.view"],
 *  "roles": {"guest": {"permissions": []}, "clerk": {"permissions": ["orders.view"]},
 *            "manager": {"inherits": ["clerk"], "permissions": []},
 *            "support": {"permissions": []}, "owner": {"permissions": "*", "except": []}}}
 * ```
 *
 * Sprawdza: strefę z logowaniem w rejestrze, role obowiązkowe (RequiredRole), nazwy uprawnień `zasób.akcja`
 * i ról w kebab-case, uprawnienia ról i `except` z listy `permissions`, `inherits` do istniejących ról bez cykli.
 * Etykiety nie należą do pliku: dają je tłumaczenia `permissions.<strefa>.*` i `roles.<strefa>.*`.
 */
final readonly class PermissionFileParser
{
    public const string ALL = '*';

    private const string PERMISSION_PATTERN = '/^[a-z][a-z0-9]*(-[a-z0-9]+)*\.[a-z][a-z0-9]*(-[a-z0-9]+)*$/';

    private const string ROLE_PATTERN = '/^[a-z][a-z0-9]*(-[a-z0-9]+)*$/';

    public function __construct(
        private ZoneRegistry $zones,
    ) {}

    /**
     * @throws InvalidPermissionFile
     */
    public function parse(string $path): ZoneRoleSet
    {
        $data = $this->decode($path);
        $zone = $this->zone($path, $data['zone'] ?? null);
        $permissions = $this->names($path, $data['permissions'] ?? null, 'permissions', self::PERMISSION_PATTERN);
        $roles = $this->roles($path, $data['roles'] ?? null, $permissions);

        $flattened = [];

        foreach (array_keys($roles) as $role) {
            $flattened[$role] = $this->flatten($path, $role, $roles, $permissions, []);
        }

        return new ZoneRoleSet($zone, $permissions, $flattened);
    }

    /**
     * @return array<array-key, mixed>
     */
    private function decode(string $path): array
    {
        $json = is_file($path) ? file_get_contents($path) : false;

        if ($json === false) {
            throw InvalidPermissionFile::in($path, 'the file cannot be read.');
        }

        try {
            $data = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw InvalidPermissionFile::in($path, 'invalid JSON (' . $exception->getMessage() . ').');
        }

        return is_array($data) ? $data : throw InvalidPermissionFile::in($path, 'the root must be an object.');
    }

    private function zone(string $path, mixed $zone): string
    {
        if (! is_string($zone) || ! $this->zones->has($zone) || ! $this->zones->get($zone)->requiresLogin()) {
            throw InvalidPermissionFile::in($path, '"zone" must name a registered zone with sign-in.');
        }

        return $zone;
    }

    /**
     * @param list<string>|null $allowed Dozwolone nazwy; null = dowolne pasujące do wzorca.
     *
     * @return list<string>
     */
    private function names(string $path, mixed $names, string $field, string $pattern, ?array $allowed = null): array
    {
        if (! is_array($names) || ! array_is_list($names)) {
            throw InvalidPermissionFile::in($path, "\"{$field}\" must be a list.");
        }

        $valid = [];

        foreach ($names as $name) {
            if (! is_string($name) || preg_match($pattern, $name) !== 1) {
                throw InvalidPermissionFile::in($path, "\"{$field}\" has an invalid name [" . (is_scalar($name) ? (string) $name : gettype($name)) . '].');
            }

            if ($allowed !== null && ! in_array($name, $allowed, true)) {
                throw InvalidPermissionFile::in($path, "\"{$field}\" names [{$name}], which is not listed in \"permissions\".");
            }

            $valid[] = $name;
        }

        return count($valid) === count(array_unique($valid)) ? $valid : throw InvalidPermissionFile::in($path, "\"{$field}\" has duplicates.");
    }

    /**
     * @param list<string> $permissions
     *
     * @return array<string, array{permissions: list<string>|null, except: list<string>, inherits: list<string>}>
     */
    private function roles(string $path, mixed $roles, array $permissions): array
    {
        if (! is_array($roles) || array_is_list($roles)) {
            throw InvalidPermissionFile::in($path, '"roles" must be an object of roles.');
        }

        $parsed = [];

        foreach ($roles as $name => $role) {
            $name = (string) $name;

            if (preg_match(self::ROLE_PATTERN, $name) !== 1 || ! is_array($role)) {
                throw InvalidPermissionFile::in($path, "role [{$name}] must be a kebab-case name with an object.");
            }

            $own = $role['permissions'] ?? [];

            $parsed[$name] = [
                'permissions' => $own === self::ALL ? null : $this->names($path, $own, "roles.{$name}.permissions", self::PERMISSION_PATTERN, $permissions),
                'except' => $this->names($path, $role['except'] ?? [], "roles.{$name}.except", self::PERMISSION_PATTERN, $permissions),
                'inherits' => $this->names($path, $role['inherits'] ?? [], "roles.{$name}.inherits", self::ROLE_PATTERN),
            ];
        }

        foreach (RequiredRole::cases() as $required) {
            if (! isset($parsed[$required->value])) {
                throw InvalidPermissionFile::in($path, "the required role [{$required->value}] is missing.");
            }
        }

        return $parsed;
    }

    /**
     * Uprawnienia roli razem z odziedziczonymi; `*` to wszystkie uprawnienia strefy, `except` je odejmuje.
     *
     * @param array<string, array{permissions: list<string>|null, except: list<string>, inherits: list<string>}> $roles
     * @param list<string> $permissions
     * @param list<string> $path Role na ścieżce dziedziczenia (wykrywanie cyklu).
     *
     * @return list<string>
     */
    private function flatten(string $file, string $role, array $roles, array $permissions, array $path): array
    {
        if (in_array($role, $path, true)) {
            throw InvalidPermissionFile::in($file, 'inheritance cycle: ' . implode(' → ', [...$path, $role]) . '.');
        }

        $definition = $roles[$role] ?? throw InvalidPermissionFile::in($file, 'role [' . end($path) . "] inherits the unknown role [{$role}].");
        $granted = $definition['permissions'] ?? $permissions;

        foreach ($definition['inherits'] as $parent) {
            $granted = [...$granted, ...$this->flatten($file, $parent, $roles, $permissions, [...$path, $role])];
        }

        $granted = array_values(array_diff(array_unique($granted), $definition['except']));
        sort($granted);

        return $granted;
    }
}
