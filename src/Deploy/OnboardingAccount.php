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
 * Konto zakładane przy onboardingu poleceniem strefy `<strefa>:account:create` (konwencja mainboard);
 * istniejące konto jest pomijane (`--skip-existing`), więc onboarding można powtarzać.
 */
final readonly class OnboardingAccount
{
    private const string COMMAND = '%s:account:create';

    private const string SKIP_EXISTING = '--skip-existing';

    /**
     * @param  list<string>  $roles  Role konta (`--role`).
     * @param  string|null  $organization  Token organizacji ról (`--organization`).
     */
    public function __construct(
        public string $zone,
        public string $email,
        public string $name,
        public array $roles = [],
        public ?string $organization = null,
    ) {}

    /**
     * Argumenty `php artisan` zakładające konto.
     *
     * @return list<string>
     */
    public function artisanArguments(): array
    {
        return [
            sprintf(self::COMMAND, $this->zone),
            $this->email,
            $this->name,
            ...array_map(fn(string $role): string => "--role={$role}", $this->roles),
            ...($this->organization === null ? [] : ["--organization={$this->organization}"]),
            self::SKIP_EXISTING,
        ];
    }
}
