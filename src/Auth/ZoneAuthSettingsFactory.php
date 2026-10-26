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

namespace Simplysmart\Admiro\Auth;

use Illuminate\Config\Repository;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Simplysmart\Admiro\Contracts\SignInStep;
use Simplysmart\Admiro\Enums\AuthLayout;

/**
 * Buduje ZoneAuthSettings: `admiro.auth` scalone rekurencyjnie z kluczem `auth` configu strefy
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
        $settings = array_replace_recursive($this->config->array('admiro.auth'), $overrides);

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
            pinLength: new PinLength(Arr::integer($settings, 'pin.min_length'), Arr::integer($settings, 'pin.max_length')),
            pinStageTtlMinutes: Arr::integer($settings, 'pin.stage_ttl_minutes'),
            pinMaxAttempts: Arr::integer($settings, 'pin.max_attempts'),
            lockoutMinutes: $this->lockoutMinutes(Arr::array($settings, 'pin.lockout_minutes')),
            limits: $this->limits(Arr::array($settings, 'limits')),
            routeThrottlePerMinute: Arr::integer($settings, 'route_throttle_per_minute'),
            challengePruneHours: Arr::integer($settings, 'challenge_prune_hours'),
            trustedDevicesEnabled: Arr::boolean($settings, 'trusted_devices.enabled'),
            trustedDeviceLifetimeDays: max(1, Arr::integer($settings, 'trusted_devices.lifetime_days')),
            trustedDevicesPerAccount: max(1, Arr::integer($settings, 'trusted_devices.max_per_account')),
            layout: $this->layout(Arr::string($settings, 'layout')),
            brand: $this->brand(Arr::get($settings, 'brand')),
            logo: $this->logo(Arr::get($settings, 'logo')),
            homeLink: Arr::boolean($settings, 'home_link', true),
            cookiePolicyFirst: Arr::boolean($settings, 'legal.cookie_policy_first', false),
            cookieSettingsLink: Arr::boolean($settings, 'legal.cookie_settings', true),
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
     * Logo strefy z `auth.logo` (ścieżka pliku SVG osadzanego inline) albo null, gdy strefa go nie ma.
     *
     * @throws InvalidArgumentException Gdy wartość nie jest ścieżką istniejącego pliku SVG.
     */
    private function logo(mixed $logo): ?string
    {
        if ($logo === null) {
            return null;
        }

        if (! is_string($logo) || ! is_file($logo) || strtolower(pathinfo($logo, PATHINFO_EXTENSION)) !== 'svg') {
            throw new InvalidArgumentException('auth.logo must be the path of an existing SVG file.');
        }

        return $logo;
    }

    /**
     * Baner logowania z `auth.brand` (`light`, opcjonalnie `dark` i `alt`) albo null, gdy strefa go nie ma.
     *
     * @throws InvalidArgumentException Gdy baner nie ma pliku `light` albo plik nie istnieje.
     */
    private function brand(mixed $brand): ?AuthBrand
    {
        if (! is_array($brand) || ($brand['light'] ?? null) === null) {
            return null;
        }

        $light = $brand['light'];
        $dark = $brand['dark'] ?? null;
        $alt = $brand['alt'] ?? '';

        if (! is_string($light) || ($dark !== null && ! is_string($dark)) || ! is_string($alt)) {
            throw new InvalidArgumentException('auth.brand needs a `light` image path, optionally `dark` and an `alt` text key.');
        }

        return new AuthBrand($light, $dark, $alt);
    }

    /**
     * @throws InvalidArgumentException Gdy układ nie jest przypadkiem AuthLayout.
     */
    private function layout(string $layout): AuthLayout
    {
        return AuthLayout::tryFrom($layout) ?? throw new InvalidArgumentException("auth.layout [{$layout}] must be one of: " . implode(', ', array_column(AuthLayout::cases(), 'value')) . '.');
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
