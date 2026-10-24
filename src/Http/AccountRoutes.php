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
use Simplysmart\Mainboard\Http\Controllers\Account\SecurityController;
use Simplysmart\Mainboard\Http\Controllers\Account\TrustedDeviceController;
use Simplysmart\Mainboard\Http\Controllers\Account\ProfileController;
use Simplysmart\Mainboard\Http\Controllers\Account\RoleSwitchController;
use Simplysmart\Mainboard\Zones\AccessTier;
use Simplysmart\Mainboard\Zones\ZoneDefinition;

/**
 * Trasy konta zalogowanego w strefie (poziom Authenticated, pod `account/`): profil (imię i nazwisko, język, kolor
 * awatara), strona bezpieczeństwa (zmiana PIN-u, historia konta, wylogowanie wszędzie; dawne `pin` i `devices`
 * przekierowują do jej sekcji), wygląd (motyw i kolorystyka), w strefie z zaufanymi urządzeniami ich odwołanie, w strefie
 * z zespołami przełączenie organizacji, a w strefie z `role_contexts` przełączenie aktywnej roli. Każda zmiana ma limit
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

    /** Przełączenie aktywnej roli (kontekstu) w strefie z `role_contexts`. */
    public const string ROLE_SWITCH = 'account.role.switch';

    /** Wygląd: motyw i kolorystyka (ustawienia konta). */
    public const string APPEARANCE = 'account.appearance';

    /** Widok strony wyglądu. */
    public const string APPEARANCE_VIEW = 'mainboard::account.appearance';

    public const string PROFILE = 'account.profile';

    public const string PROFILE_UPDATE = 'account.profile.update';

    /** Strona bezpieczeństwa: PIN, zaufane urządzenia, historia konta, wylogowanie wszędzie. */
    public const string SECURITY = 'account.security';

    public const string SIGN_OUT_EVERYWHERE = 'account.security.sign-out-everywhere';

    /** Kotwice sekcji strony bezpieczeństwa (`#pin`, `#devices`). */
    public const string SECTION_PIN = 'pin';

    public const string SECTION_DEVICES = 'devices';

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
            $this->router->get('security', [SecurityController::class, 'show'])->name(self::SECURITY);
            $this->router->view('appearance', self::APPEARANCE_VIEW)->name(self::APPEARANCE);
            $this->router->delete('security/sessions', [SecurityController::class, 'signOutEverywhere'])->middleware($throttle)->name(self::SIGN_OUT_EVERYWHERE);
            $this->router->get('pin', [SecurityController::class, 'section'])->defaults('section', self::SECTION_PIN)->name(self::PIN);
            $this->router->post('pin/code', [PinChangeController::class, 'sendCode'])->middleware($throttle)->name(self::PIN_CODE);
            $this->router->put('pin', [PinChangeController::class, 'update'])->middleware($throttle)->name(self::PIN_UPDATE);

            if ($zone->auth()->trustedDevicesEnabled) {
                $this->router->get('devices', [SecurityController::class, 'section'])->defaults('section', self::SECTION_DEVICES)->name(self::DEVICES);
                $this->router->delete('devices', [TrustedDeviceController::class, 'destroyAll'])->middleware($throttle)->name(self::DEVICES_REVOKE_ALL);
                $this->router->delete('devices/{device}', [TrustedDeviceController::class, 'destroy'])->middleware($throttle)->name(self::DEVICES_REVOKE);
            }

            if ($zone->teams) {
                $this->router->post('organization', OrganizationSwitchController::class)->middleware($throttle)->name(self::ORGANIZATION_SWITCH);
            }

            if ($zone->roleContexts) {
                $this->router->post('role', RoleSwitchController::class)->middleware($throttle)->name(self::ROLE_SWITCH);
            }
        });
    }
}
