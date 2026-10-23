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

use Illuminate\Contracts\Events\Dispatcher;
use Simplysmart\Mainboard\Auth\AccountKey;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Simplysmart\Mainboard\Auth\Events\TrustedDeviceRegistered;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Contracts\SignInAccount;
use Simplysmart\Mainboard\Contracts\TrustedDeviceRepository;

/**
 * Zapamiętuje przeglądarkę jako zaufane urządzenie konta (po kroku kodu z e-maila): zapis z HMAC validatora,
 * ciasteczko do końca zaufania i powiadomienie. Ponad limit urządzeń konta odwołuje najstarsze.
 */
final readonly class RegisterTrustedDevice
{
    public function __construct(
        private TrustedDeviceRepository $devices,
        private DeviceTokenIssuer $tokens,
        private DeviceNameResolver $names,
        private DeviceCookie $cookie,
        private CurrentZone $zone,
        private Dispatcher $events,
    ) {}

    public function handle(SignInAccount $account, Request $request): void
    {
        $zone = $this->zone->get();
        $settings = $zone->auth();
        $accountId = AccountKey::of($account);

        foreach (array_slice($this->devices->activeFor($accountId), $settings->trustedDevicesPerAccount - 1) as $oldest) {
            $this->devices->revoke($oldest->id);
        }

        $token = $this->tokens->issue();
        $expiresAt = Carbon::now()->addDays($settings->trustedDeviceLifetimeDays);
        $name = $this->names->name($request->userAgent());

        $this->devices->create($accountId, $token->selector, $this->tokens->hash($token), $name, $request->ip(), $request->userAgent(), $expiresAt);
        $this->cookie->queue($zone, $token, $expiresAt);

        $this->events->dispatch(new TrustedDeviceRegistered($zone->name, $account, $name, $request->ip()));
    }
}
