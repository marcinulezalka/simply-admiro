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

use Simplysmart\Mainboard\Auth\AccountKey;
use Illuminate\Http\Request;
use Simplysmart\Mainboard\Auth\AccessRevocationReason;
use Simplysmart\Mainboard\Auth\RevokeAccountAccess;
use Simplysmart\Mainboard\Contracts\AccountLocator;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Contracts\TrustedDeviceRepository;

/**
 * Czy przeglądarka jest zaufanym urządzeniem konta o podanym adresie (logowanie pominie wtedy kod z e-maila).
 *
 * - Bez ciasteczka, z ciasteczkiem innego konta albo nieaktywnego urządzenia: nie (zwykły kod, nic nie wycieka).
 * - Zgodny selector, ale stary validator: ktoś użył skopiowanego ciasteczka, więc dostęp do konta jest
 *   unieważniany (sesje i wszystkie urządzenia), a właściciel dostaje e-mail.
 * - Inna przeglądarka lub system niż zapamiętane: urządzenie przestaje być zaufane.
 * - Zaufane: validator jest rotowany (nowa wartość w bazie i w ciasteczku).
 */
final readonly class RecognizeTrustedDevice
{
    public function __construct(
        private TrustedDeviceRepository $devices,
        private DeviceTokenIssuer $tokens,
        private DeviceNameResolver $names,
        private DeviceCookie $cookie,
        private AccountLocator $accounts,
        private RevokeAccountAccess $revoke,
        private CurrentZone $zone,
    ) {}

    public function handle(string $email, Request $request): bool
    {
        $zone = $this->zone->get();
        $token = $this->cookie->read($zone, $request);

        if (! $zone->auth()->trustedDevicesEnabled || $token === null) {
            return false;
        }

        $device = $this->devices->findActiveBySelector($token->selector);
        $account = $this->accounts->findByEmail($email);
        $accountId = $account === null ? null : AccountKey::from($account->getAuthIdentifier());

        if ($device === null || $account === null || $accountId === null || ! $device->belongsTo($accountId)) {
            return false;
        }

        if (! $this->tokens->matches($token, $device->validatorHash)) {
            $this->cookie->forget($zone);
            $this->revoke->handle($zone, $account, AccessRevocationReason::DeviceTokenReused);

            return false;
        }

        if (! $account->canSignIn()) {
            return false;
        }

        if (! $this->names->sameDevice($device->userAgent, $request->userAgent())) {
            $this->devices->revoke($device->id);
            $this->cookie->forget($zone);

            return false;
        }

        $rotated = $this->tokens->rotate($token);
        $this->devices->rotate($device->id, $this->tokens->hash($rotated));
        $this->cookie->queue($zone, $rotated, $device->expiresAt);

        return true;
    }
}
