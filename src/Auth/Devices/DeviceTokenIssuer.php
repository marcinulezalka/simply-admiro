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

namespace Simplysmart\Mainboard\Auth\Devices;

/**
 * Losuje tokeny urządzeń (`random_bytes`: selector 16 B, validator 32 B) i liczy HMAC-SHA256 validatora
 * z kluczem aplikacji; porównanie w stałym czasie. Wyciek bazy nie daje więc działających ciasteczek.
 */
final readonly class DeviceTokenIssuer
{
    private const int SELECTOR_BYTES = 16;

    private const int VALIDATOR_BYTES = 32;

    public function __construct(
        #[\SensitiveParameter]
        private string $key,
    ) {}

    public function issue(): DeviceToken
    {
        return new DeviceToken(bin2hex(random_bytes(self::SELECTOR_BYTES)), bin2hex(random_bytes(self::VALIDATOR_BYTES)));
    }

    /**
     * Ten sam selector, nowy validator (rotacja przy każdym użyciu urządzenia).
     */
    public function rotate(DeviceToken $token): DeviceToken
    {
        return new DeviceToken($token->selector, bin2hex(random_bytes(self::VALIDATOR_BYTES)));
    }

    public function hash(DeviceToken $token): string
    {
        return hash_hmac('sha256', $token->validator, $this->key);
    }

    public function matches(DeviceToken $token, string $hash): bool
    {
        return hash_equals($hash, $this->hash($token));
    }
}
