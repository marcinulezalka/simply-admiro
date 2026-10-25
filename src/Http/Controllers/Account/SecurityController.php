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

namespace Simplysmart\Mainboard\Http\Controllers\Account;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Simplysmart\Mainboard\Auth\AccessRevocationReason;
use Simplysmart\Mainboard\Auth\Devices\DeviceCookie;
use Simplysmart\Mainboard\Auth\PinLength;
use Simplysmart\Mainboard\Auth\Recovery\RecoveryEmailRegistration;
use Simplysmart\Mainboard\Auth\RevokeAccountAccess;
use Simplysmart\Mainboard\Auth\SignIn\SignOut;
use Simplysmart\Mainboard\Contracts\CurrentAccount;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Contracts\TrustedDeviceRepository;
use Simplysmart\Mainboard\Http\AccountRoutes;
use Simplysmart\Mainboard\Queries\Audit\AccountHistory;
use Simplysmart\Mainboard\Zones\ZoneRedirects;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Bezpieczeństwo zalogowanego konta na jednej stronie: zmiana PIN-u (sekcja `pin`), zapasowy adres e-mail
 * (sekcja `recovery`, RecoveryEmailController), zaufane urządzenia
 * (sekcja `devices`, gdy strefa je ma), ostatnie zdarzenia konta i wylogowanie ze wszystkich urządzeń.
 * Dawne adresy sekcji (`account.pin`, `account.devices`) przekierowują do kotwicy na tej stronie.
 */
final readonly class SecurityController
{
    public function __construct(
        private CurrentZone $zone,
        private CurrentAccount $account,
        private Redirector $redirect,
    ) {}

    public function show(Request $request, TrustedDeviceRepository $devices, DeviceCookie $cookie, AccountHistory $history, RecoveryEmailRegistration $recovery): View
    {
        $zone = $this->zone->get();
        $settings = $zone->auth();
        $accountId = $this->account->id() ?? throw new AccessDeniedHttpException();

        return view('mainboard::account.security', [
            'currentPinLength' => PinLength::accepted(),
            'pinLength' => $settings->pinLength,
            'codeLength' => $settings->codeLength,
            'devices' => $settings->trustedDevicesEnabled ? $devices->activeFor($accountId) : null,
            'currentSelector' => $settings->trustedDevicesEnabled ? $cookie->read($zone, $request)?->selector : null,
            'history' => $history->recent($zone->name, $accountId),
            'recoveryEmail' => $this->account->require()->recoveryEmail(),
            'pendingRecoveryEmail' => $recovery->pending($request),
        ]);
    }

    /**
     * Dawny adres sekcji: przekierowanie do jej kotwicy na stronie bezpieczeństwa.
     */
    public function section(string $section): RedirectResponse
    {
        return self::redirectTo($this->redirect, $this->zone, $section);
    }

    /**
     * Wylogowanie ze wszystkich urządzeń: nowa epoka konta (każda sesja w strefie wygasa), odwołanie zaufanych
     * urządzeń i wylogowanie bieżącej sesji; powrót do logowania z komunikatem.
     */
    public function signOutEverywhere(Request $request, RevokeAccountAccess $revoke, SignOut $signOut): RedirectResponse
    {
        $zone = $this->zone->get();

        $revoke->handle($zone, $this->account->require(), AccessRevocationReason::SignedOutEverywhere);
        $signOut->handle($request);

        return $this->redirect->route($zone->routeName(ZoneRedirects::LOGIN_ROUTE))->with('status', text('account.signed_out_everywhere'));
    }

    /**
     * Strona bezpieczeństwa przewinięta do sekcji (`pin`, `devices`); dla kontrolerów zmian w tych sekcjach.
     */
    public static function redirectTo(Redirector $redirect, CurrentZone $zone, string $section): RedirectResponse
    {
        return $redirect->to($redirect->getUrlGenerator()->route($zone->get()->routeName(AccountRoutes::SECURITY)) . '#' . $section);
    }
}
