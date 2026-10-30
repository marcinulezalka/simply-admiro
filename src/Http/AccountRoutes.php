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

namespace Simplysmart\Admiro\Http;

use Illuminate\Routing\Router;
use Simplysmart\Admiro\Http\Controllers\Account\AppearanceController;
use Simplysmart\Admiro\Http\Controllers\Account\ContextSwitchController;
use Simplysmart\Admiro\Http\Controllers\Account\OrganizationSwitchController;
use Simplysmart\Admiro\Http\Controllers\Account\PinChangeController;
use Simplysmart\Admiro\Http\Controllers\Account\RecoveryEmailController;
use Simplysmart\Admiro\Http\Controllers\Account\ScreenLockController;
use Simplysmart\Admiro\Http\Controllers\Account\SecurityController;
use Simplysmart\Admiro\Http\Controllers\Account\TaskController;
use Simplysmart\Admiro\Http\Controllers\Account\TrustedDeviceController;
use Simplysmart\Admiro\Http\Controllers\Account\ProfileController;
use Simplysmart\Admiro\Http\Controllers\Account\RoleSwitchController;
use Simplysmart\Admiro\Zones\AccessTier;
use Simplysmart\Admiro\Zones\ZoneDefinition;

/**
 * Trasy konta zalogowanego w strefie (poziom Authenticated, pod `account/`): profil (imię i nazwisko, język, kolor
 * awatara), strona bezpieczeństwa (zmiana PIN-u, zapasowy adres e-mail, historia konta, wylogowanie wszędzie; dawne `pin` i `devices`
 * przekierowują do jej sekcji), wygląd (motyw i kolorystyka; w strefie z `appearance.account` także zapis w koncie
 * i przywrócenie domyślnego), w strefie z zaufanymi urządzeniami ich odwołanie, w strefie
 * z zespołami przełączenie organizacji, a w strefie z `role_contexts` przełączenie aktywnej roli; do tego zadania TODO konta (komenda `todo:` z pola komend menu błyskawicy i przełączenie
 * wykonania z listy pod powiadomieniami). Każda zmiana (poza wyglądem i przełączeniem wykonania zadania — wiele kliknięć) ma limit
 * `admiro.sign-in`.
 */
final readonly class AccountRoutes
{
    public const string PIN = 'account.pin';

    public const string PIN_CODE = 'account.pin.code';

    public const string PIN_UPDATE = 'account.pin.update';

    /** Zapasowy adres e-mail: kod na nowy adres, potwierdzenie, usunięcie (sekcja `#recovery`). */
    public const string RECOVERY_EMAIL_CODE = 'account.recovery-email.code';

    public const string RECOVERY_EMAIL_UPDATE = 'account.recovery-email.update';

    public const string RECOVERY_EMAIL_DESTROY = 'account.recovery-email.destroy';

    public const string DEVICES = 'account.devices';

    public const string DEVICES_REVOKE = 'account.devices.revoke';

    public const string DEVICES_REVOKE_ALL = 'account.devices.revoke-all';

    public const string ORGANIZATION_SWITCH = 'account.organization.switch';

    /** Przełączenie kontekstu jednym kliknięciem: organizacja i rola (Zones\ZoneContexts), w strefie z `teams`. */
    public const string CONTEXT_SWITCH = 'account.context.switch';

    /** Przełączenie aktywnej roli (kontekstu) w strefie z `role_contexts`. */
    public const string ROLE_SWITCH = 'account.role.switch';

    /** Wygląd: motyw i kolorystyka (ustawienia konta). */
    public const string APPEARANCE = 'account.appearance';

    /** Zapis wyglądu w koncie (strefa z `appearance.account`): PUT z ustawieniami, DELETE przywraca domyślny wygląd. */
    public const string APPEARANCE_UPDATE = 'account.appearance.update';

    public const string APPEARANCE_RESET = 'account.appearance.reset';

    /** Widok strony wyglądu. */
    public const string APPEARANCE_VIEW = 'admiro::account.appearance';

    public const string PROFILE = 'account.profile';

    public const string PROFILE_UPDATE = 'account.profile.update';

    /** Strona bezpieczeństwa: PIN, zaufane urządzenia, historia konta, wylogowanie wszędzie. */
    public const string SECURITY = 'account.security';

    /** Blokada ekranu (POST), ekran PIN-u i odblokowanie (Http\Controllers\Account\ScreenLockController). */
    public const string LOCK = 'account.lock';

    public const string UNLOCK = 'account.unlock';

    public const string UNLOCK_STORE = 'account.unlock.store';

    public const string SIGN_OUT_EVERYWHERE = 'account.security.sign-out-everywhere';

    /** Kotwice sekcji strony bezpieczeństwa (`#pin`, `#devices`, `#recovery`, `#lock` — karty blokowania dostępu). */
    public const string SECTION_PIN = 'pin';

    public const string SECTION_DEVICES = 'devices';

    public const string SECTION_RECOVERY = 'recovery';

    /** Kotwica „Wyloguj wszędzie”; po wejściu na nią podświetlone są karty blokowania dostępu (urządzenia, wylogowanie). */
    public const string SECTION_LOCK = 'lock';

    /** Zadania TODO konta: komenda z pola komend menu błyskawicy (POST) i przełączenie wykonania zadania (PATCH). */
    public const string TASKS_STORE = 'account.tasks.store';

    public const string TASKS_TOGGLE = 'account.tasks.toggle';

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
            $this->router->post('lock', [ScreenLockController::class, 'lock'])->name(self::LOCK);
            $this->router->get('unlock', [ScreenLockController::class, 'show'])->name(self::UNLOCK);
            $this->router->post('unlock', [ScreenLockController::class, 'unlock'])->middleware($throttle)->name(self::UNLOCK_STORE);
            $this->router->view('appearance', self::APPEARANCE_VIEW)->name(self::APPEARANCE);
            $this->router->post('tasks', [TaskController::class, 'store'])->middleware($throttle)->name(self::TASKS_STORE);
            $this->router->patch('tasks/{task}', [TaskController::class, 'toggle'])->name(self::TASKS_TOGGLE);
            $this->router->delete('security/sessions', [SecurityController::class, 'signOutEverywhere'])->middleware($throttle)->name(self::SIGN_OUT_EVERYWHERE);
            $this->router->get('pin', [SecurityController::class, 'section'])->defaults('section', self::SECTION_PIN)->name(self::PIN);
            $this->router->post('pin/code', [PinChangeController::class, 'sendCode'])->middleware($throttle)->name(self::PIN_CODE);
            $this->router->put('pin', [PinChangeController::class, 'update'])->middleware($throttle)->name(self::PIN_UPDATE);
            $this->router->post('recovery-email/code', [RecoveryEmailController::class, 'sendCode'])->middleware($throttle)->name(self::RECOVERY_EMAIL_CODE);
            $this->router->put('recovery-email', [RecoveryEmailController::class, 'update'])->middleware($throttle)->name(self::RECOVERY_EMAIL_UPDATE);
            $this->router->delete('recovery-email', [RecoveryEmailController::class, 'destroy'])->middleware($throttle)->name(self::RECOVERY_EMAIL_DESTROY);

            if ($zone->auth()->trustedDevicesEnabled) {
                $this->router->get('devices', [SecurityController::class, 'section'])->defaults('section', self::SECTION_DEVICES)->name(self::DEVICES);
                $this->router->delete('devices', [TrustedDeviceController::class, 'destroyAll'])->middleware($throttle)->name(self::DEVICES_REVOKE_ALL);
                $this->router->delete('devices/{device}', [TrustedDeviceController::class, 'destroy'])->middleware($throttle)->name(self::DEVICES_REVOKE);
            }

            if ($zone->appearance->account) {
                $this->router->put('appearance', [AppearanceController::class, 'update'])->name(self::APPEARANCE_UPDATE);
                $this->router->delete('appearance', [AppearanceController::class, 'destroy'])->name(self::APPEARANCE_RESET);
            }

            if ($zone->teams) {
                $this->router->post('organization', OrganizationSwitchController::class)->middleware($throttle)->name(self::ORGANIZATION_SWITCH);
                $this->router->post('context', ContextSwitchController::class)->middleware($throttle)->name(self::CONTEXT_SWITCH);
            }

            if ($zone->roleContexts) {
                $this->router->post('role', RoleSwitchController::class)->middleware($throttle)->name(self::ROLE_SWITCH);
            }
        });
    }
}
