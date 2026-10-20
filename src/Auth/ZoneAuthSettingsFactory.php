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

namespace Simplysmart\Mainboard\Auth;

use Illuminate\Config\Repository;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Simplysmart\Mainboard\Contracts\SignInStep;

/**
 * Buduje ZoneAuthSettings: `mainboard.auth` scalone rekurencyjnie z kluczem `auth` configu strefy
 * (lista kroków i lista czasów blokad są zastępowane w całości).
 */
final readonly class ZoneAuthSettingsFactory
{
    public function __construct(
        private Repository $config,
    ) {}

    /**
     * @param array<array-key, mixed> $overrides
     *
     * @throws InvalidArgumentException Gdy ustawienia są niepoprawne.
     */
    public function make(array $overrides): ZoneAuthSettings
    {
        $settings = array_replace_recursive($this->config->array('mainboard.auth'), $overrides);

        foreach (['steps', 'pin.lockout_minutes'] as $list) {
            if (Arr::has($overrides, $list)) {
                Arr::set($settings, $list, Arr::get($overrides, $list));
            }
        }

        return new ZoneAuthSettings(
            steps: $this->steps(Arr::array($settings, 'steps')),
            codeLength: Arr::integer($settings, 'code.length'),
            codeTtlMinutes: Arr::integer($settings, 'code.ttl_minutes'),
            codeMaxAttempts: Arr::integer($settings, 'code.max_attempts'),
            codeResendCooldownSeconds: Arr::integer($settings, 'code.resend_cooldown_seconds'),
            pinLength: max(4, Arr::integer($settings, 'pin.length')),
            pinStageTtlMinutes: Arr::integer($settings, 'pin.stage_ttl_minutes'),
            pinMaxAttempts: Arr::integer($settings, 'pin.max_attempts'),
            lockoutMinutes: $this->lockoutMinutes(Arr::array($settings, 'pin.lockout_minutes')),
            limits: $this->limits(Arr::array($settings, 'limits')),
            routeThrottlePerMinute: Arr::integer($settings, 'route_throttle_per_minute'),
            challengePruneHours: Arr::integer($settings, 'challenge_prune_hours'),
        );
    }

    /**
     * @param array<array-key, mixed> $steps
     *
     * @return list<class-string<SignInStep>>
     */
    private function steps(array $steps): array
    {
        $classes = [];

        foreach ($steps as $step) {
            if (! is_string($step) || ! is_a($step, SignInStep::class, true)) {
                throw new InvalidArgumentException('Every sign-in step must implement ' . SignInStep::class . '.');
            }

            $classes[] = $step;
        }

        return $classes === [] ? throw new InvalidArgumentException('A zone with sign-in needs at least one step.') : $classes;
    }

    /**
     * @param array<array-key, mixed> $minutes
     *
     * @return list<int>
     */
    private function lockoutMinutes(array $minutes): array
    {
        $values = array_values(array_filter($minutes, static fn(mixed $value): bool => is_int($value) && $value > 0));

        return $values === [] ? throw new InvalidArgumentException('auth.pin.lockout_minutes needs at least one positive number.') : $values;
    }

    /**
     * @param array<array-key, mixed> $limits
     *
     * @return array<string, array{max: int, decay_minutes: int}>
     */
    private function limits(array $limits): array
    {
        $resolved = [];

        foreach (SignInLimit::cases() as $limit) {
            $values = Arr::array($limits, $limit->value);
            $resolved[$limit->value] = ['max' => Arr::integer($values, 'max'), 'decay_minutes' => Arr::integer($values, 'decay_minutes')];
        }

        return $resolved;
    }
}
