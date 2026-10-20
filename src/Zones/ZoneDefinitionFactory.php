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

namespace Simplysmart\Mainboard\Zones;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Simplysmart\Mainboard\Auth\ZoneAuthSettingsFactory;

/**
 * Buduje ZoneDefinition z configu paczki strefy, uzupełniając go domyślnymi wartościami Core.
 *
 * Config strefy: name (wymagane), slug, guard, provider, model, session (klucze ZoneSessionSettings),
 * auth (nadpisania `mainboard.auth`, tylko strefa z modelem).
 */
final readonly class ZoneDefinitionFactory
{
    private const string NAME_PATTERN = '/^[a-z][a-z0-9_]*$/';

    public function __construct(
        private Repository $config,
        private ZoneAuthSettingsFactory $authSettings,
    ) {}

    /**
     * @param array<array-key, mixed> $config
     *
     * @throws InvalidArgumentException Gdy config strefy jest niepoprawny.
     */
    public function make(array $config, string $routeNamePrefix, string $routesPath): ZoneDefinition
    {
        $name = Arr::string($config, 'name');

        foreach (['name' => $name, 'route name prefix' => $routeNamePrefix] as $label => $value) {
            if (preg_match(self::NAME_PATTERN, $value) !== 1) {
                throw new InvalidArgumentException("Zone {$label} [{$value}] must match " . self::NAME_PATTERN . '.');
            }
        }

        $model = $this->model($config['model'] ?? null, $name);

        return new ZoneDefinition(
            name: $name,
            routeNamePrefix: $routeNamePrefix,
            slug: trim(Arr::string($config, 'slug', $name), '/'),
            guard: Arr::string($config, 'guard', $name),
            provider: Arr::string($config, 'provider', ZoneTable::Accounts->for($name)),
            model: $model,
            routesPath: rtrim($routesPath, '/'),
            session: $this->session($name, $model !== null, Arr::array($config, 'session', [])),
            authSettings: $model !== null ? $this->authSettings->make(Arr::array($config, 'auth', [])) : null,
        );
    }

    /**
     * @return class-string<Authenticatable>|null
     */
    private function model(mixed $model, string $zone): ?string
    {
        if ($model === null) {
            return null;
        }

        if (! is_string($model) || ! is_a($model, Authenticatable::class, true)) {
            throw new InvalidArgumentException("Zone [{$zone}] model must be a class implementing " . Authenticatable::class . '.');
        }

        return $model;
    }

    /**
     * Strefa z logowaniem dostaje własną tabelę sesji, krótki czas życia, SameSite=Strict
     * i szyfrowanie; strefa publiczna tylko własną nazwę ciasteczka i SameSite=Lax.
     *
     * @param array<array-key, mixed> $session
     */
    private function session(string $zone, bool $requiresLogin, array $session): ZoneSessionSettings
    {
        return new ZoneSessionSettings(
            cookie: Arr::string($session, 'cookie', $zone . '_session'),
            table: $this->nullableString($session, 'table', $requiresLogin ? ZoneTable::Sessions->for($zone) : null),
            lifetime: $this->nullableInt($session, 'lifetime', $requiresLogin ? $this->config->integer('mainboard.zones.session_lifetime') : null),
            expireOnClose: $this->nullableBool($session, 'expire_on_close', $requiresLogin ? true : null),
            sameSite: $this->nullableString($session, 'same_site', $requiresLogin ? 'strict' : 'lax'),
            encrypt: $this->nullableBool($session, 'encrypt', $requiresLogin ? true : null),
        );
    }

    /**
     * @param array<array-key, mixed> $values
     */
    private function nullableString(array $values, string $key, ?string $default): ?string
    {
        return array_key_exists($key, $values) && $values[$key] !== null ? Arr::string($values, $key) : $default;
    }

    /**
     * @param array<array-key, mixed> $values
     */
    private function nullableInt(array $values, string $key, ?int $default): ?int
    {
        return array_key_exists($key, $values) && $values[$key] !== null ? Arr::integer($values, $key) : $default;
    }

    /**
     * @param array<array-key, mixed> $values
     */
    private function nullableBool(array $values, string $key, ?bool $default): ?bool
    {
        return array_key_exists($key, $values) && $values[$key] !== null ? Arr::boolean($values, $key) : $default;
    }
}
