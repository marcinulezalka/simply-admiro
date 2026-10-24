<?php

declare(strict_types=1);

/**
 * This file is part of simplysmart/tools.
 *
 * Copyright (c) 2026 Marcin Ulezalka <marcin.ulezalka@simplysmart.pl>
 *
 * Licensed under the Simplysmart Restricted Source License 1.0.
 * See the LICENSE file distributed with this source code.
 */

namespace Simplysmart\Tools\Deploy;

/**
 * Wartości pierwszej instalacji z `infrastructure/deploy.onboarding.env`: konta z kluczy
 * `ONBOARD_ACCOUNT_<n>_ZONE`, `_EMAIL`, `_NAME` (wymagane), `_ROLES` (po przecinku) i `_ORGANIZATION`.
 */
final readonly class OnboardingConfig
{
    public const string DEFAULT_FILE = 'infrastructure/deploy.onboarding.env';

    private const string ACCOUNT_KEY = '/^ONBOARD_ACCOUNT_(\d+)_([A-Z_]+)$/';

    private const string ZONE = 'ZONE';

    private const string EMAIL = 'EMAIL';

    private const string NAME = 'NAME';

    private const string ROLES = 'ROLES';

    private const string ORGANIZATION = 'ORGANIZATION';

    private const array FIELDS = [self::ZONE, self::EMAIL, self::NAME, self::ROLES, self::ORGANIZATION];

    private const array REQUIRED = [self::ZONE, self::EMAIL, self::NAME];

    private const string ZONE_NAME = '/^[a-z0-9][a-z0-9_-]*$/';

    /**
     * @param  list<OnboardingAccount>  $accounts
     */
    public function __construct(
        public array $accounts,
    ) {}

    /**
     * @throws DeployConfigException
     */
    public static function fromFile(string $path, string $name): self
    {
        return self::fromValues(EnvFile::read($path, $name), $name);
    }

    /**
     * @param  array<string, string>  $values
     *
     * @throws DeployConfigException
     */
    public static function fromValues(array $values, string $name): self
    {
        $fields = [];

        foreach ($values as $key => $value) {
            if (preg_match(self::ACCOUNT_KEY, $key, $match) !== 1 || ! in_array($match[2], self::FIELDS, true)) {
                throw new DeployConfigException("{$name}: nieznany klucz {$key} (oczekiwano ONBOARD_ACCOUNT_<n>_" . implode('|', self::FIELDS) . ').');
            }

            $fields[(int) $match[1]][$match[2]] = trim($value);
        }

        if ($fields === []) {
            throw new DeployConfigException("{$name}: brak kont (ONBOARD_ACCOUNT_1_ZONE, _EMAIL, _NAME).");
        }

        ksort($fields);
        $accounts = [];

        foreach ($fields as $number => $account) {
            $accounts[] = self::account($account, "ONBOARD_ACCOUNT_{$number}_", $name);
        }

        return new self($accounts);
    }

    /**
     * @param  array<string, string>  $fields
     *
     * @throws DeployConfigException
     */
    private static function account(array $fields, string $prefix, string $name): OnboardingAccount
    {
        $missing = array_values(array_filter(self::REQUIRED, fn(string $field): bool => ($fields[$field] ?? '') === ''));

        if ($missing !== []) {
            throw MissingConfigException::keys($name, array_map(fn(string $field): string => $prefix . $field, $missing));
        }

        if (preg_match(self::ZONE_NAME, $fields[self::ZONE]) !== 1) {
            throw new DeployConfigException("{$name}: {$prefix}" . self::ZONE . " — nazwa strefy z małych liter, cyfr, - i _ (np. admiro).");
        }

        $organization = $fields[self::ORGANIZATION] ?? '';

        return new OnboardingAccount(
            zone: $fields[self::ZONE],
            email: $fields[self::EMAIL],
            name: $fields[self::NAME],
            roles: array_values(array_filter(array_map(trim(...), explode(',', $fields[self::ROLES] ?? '')), fn(string $role): bool => $role !== '')),
            organization: $organization === '' ? null : $organization,
        );
    }
}
