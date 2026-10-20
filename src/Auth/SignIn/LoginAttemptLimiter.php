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

namespace Simplysmart\Mainboard\Auth\SignIn;

use Illuminate\Cache\RateLimiter;
use Illuminate\Validation\ValidationException;
use Simplysmart\Mainboard\Auth\SignInLimit;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Zones\ZoneCacheKey;

/**
 * Limity prób logowania per tożsamość (IP, adres, para adres + IP), z kluczami
 * `zone:<strefa>:auth:<limit>:sha256(tożsamość)`; przekroczenie to błąd formularza, nie 429.
 */
final readonly class LoginAttemptLimiter
{
    public function __construct(
        private RateLimiter $limiter,
        private CurrentZone $zone,
    ) {}

    /**
     * @throws ValidationException Gdy limit jest wyczerpany.
     */
    public function attempt(SignInLimit $limit, string $identity, string $field): void
    {
        $zone = $this->zone->get();
        $settings = $zone->auth()->limit($limit);
        $key = ZoneCacheKey::make($zone, 'auth', $limit->value, hash('sha256', $identity));

        if ($this->limiter->tooManyAttempts($key, $settings['max'])) {
            throw ValidationException::withMessages([
                $field => text('sign_in.too_many_attempts', ['seconds' => $this->limiter->availableIn($key)]),
            ]);
        }

        $this->limiter->hit($key, $settings['decay_minutes'] * 60);
    }
}
