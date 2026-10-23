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

namespace Simplysmart\Mainboard\Http;

use Illuminate\Routing\Router;
use Simplysmart\Mainboard\Http\Controllers\Account\OrganizationSwitchController;
use Simplysmart\Mainboard\Http\Controllers\Account\PinChangeController;
use Simplysmart\Mainboard\Http\Controllers\Account\TrustedDeviceController;
use Simplysmart\Mainboard\Http\Controllers\Account\ProfileController;
use Simplysmart\Mainboard\Zones\AccessTier;
use Simplysmart\Mainboard\Zones\ZoneDefinition;

/**
 * Trasy konta zalogowanego w strefie (poziom Authenticated, pod `account/`): profil (kolor awatara), zmiana PIN-u, w strefie z zaufanymi
 * urządzeniami ich lista i odwołanie, a w strefie z zespołami przełączenie organizacji. Każda zmiana ma limit
 * `mainboard.sign-in`.
 */
final readonly class AccountRoutes
{
    public const string PIN = 'account.pin';

    public const string PIN_CODE = 'account.pin.code';

    public const string PIN_UPDATE = 'account.pin.update';

    public const string DEVICES = 'account.devices';

    public const string DEVICES_REVOKE = 'account.devices.revoke';

    public const string DEVICES_REVOKE_ALL = 'account.devices.revoke-all';

    public const string ORGANIZATION_SWITCH = 'account.organization.switch';

    public const string PROFILE = 'account.profile';

    public const string PROFILE_UPDATE = 'account.profile.update';

    private const string PREFIX = 'account';

    public function __construct(
        private Router $router,
    ) {}

    public function register(ZoneDefinition $zone): void
    {
        $throttle = 'throttle:' . SignInRoutes::THROTTLE;

        $this->router->middleware(AccessTier::Authenticated->middleware($zone))->prefix(self::PREFIX)->group(function () use ($zone, $throttle): void {
            $this->router->get('profile', [ProfileController::class, 'show'])->name(self::PROFILE);
            $this->router->put('profile', [ProfileController::class, 'update'])->middleware($throttle)->name(self::PROFILE_UPDATE);
            $this->router->get('pin', [PinChangeController::class, 'show'])->name(self::PIN);
            $this->router->post('pin/code', [PinChangeController::class, 'sendCode'])->middleware($throttle)->name(self::PIN_CODE);
            $this->router->put('pin', [PinChangeController::class, 'update'])->middleware($throttle)->name(self::PIN_UPDATE);

            if ($zone->auth()->trustedDevicesEnabled) {
                $this->router->get('devices', [TrustedDeviceController::class, 'index'])->name(self::DEVICES);
                $this->router->delete('devices', [TrustedDeviceController::class, 'destroyAll'])->middleware($throttle)->name(self::DEVICES_REVOKE_ALL);
                $this->router->delete('devices/{device}', [TrustedDeviceController::class, 'destroy'])->middleware($throttle)->name(self::DEVICES_REVOKE);
            }

            if ($zone->teams) {
                $this->router->post('organization', OrganizationSwitchController::class)->middleware($throttle)->name(self::ORGANIZATION_SWITCH);
            }
        });
    }
}
