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

namespace Simplysmart\Admiro\Http\Controllers\Account;

use Simplysmart\Admiro\Contracts\CurrentAccount;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Simplysmart\Admiro\Auth\Devices\DeviceCookie;
use Simplysmart\Admiro\Auth\Events\TrustedDevicesRevoked;
use Simplysmart\Admiro\Contracts\CurrentZone;
use Simplysmart\Admiro\Http\AccountRoutes;
use Simplysmart\Admiro\Contracts\TrustedDeviceRepository;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Zaufane urządzenia zalogowanego konta (lista na stronie bezpieczeństwa, SecurityController): odwołanie jednego
 * (po tokenie; urządzenie innego konta to 404) i wszystkich. Odwołanie bieżącego usuwa też ciasteczko.
 */
final readonly class TrustedDeviceController
{
    public const string CAUSE_MANUAL = 'manual';

    public function __construct(
        private TrustedDeviceRepository $devices,
        private DeviceCookie $cookie,
        private CurrentZone $zone,
        private CurrentAccount $account,
        private Dispatcher $events,
        private Redirector $redirect,
    ) {}

    public function destroy(Request $request, string $device): RedirectResponse
    {
        $zone = $this->zone->get();
        $accountId = $this->accountId();
        $record = $this->devices->findActiveByToken($accountId, $device) ?? throw new NotFoundHttpException();

        $this->devices->revoke($record->id);

        if ($this->cookie->read($zone, $request)?->selector === $record->selector) {
            $this->cookie->forget($zone);
        }

        $this->events->dispatch(new TrustedDevicesRevoked($zone->name, $accountId, 1, self::CAUSE_MANUAL));

        return $this->back();
    }

    public function destroyAll(): RedirectResponse
    {
        $zone = $this->zone->get();
        $accountId = $this->accountId();
        $count = $this->devices->revokeAll($accountId);

        $this->cookie->forget($zone);
        $this->events->dispatch(new TrustedDevicesRevoked($zone->name, $accountId, $count, self::CAUSE_MANUAL));

        return $this->back();
    }

    private function accountId(): int|string
    {
        return $this->account->id() ?? throw new AccessDeniedHttpException();
    }

    private function back(): RedirectResponse
    {
        return SecurityController::redirectTo($this->redirect, $this->zone, AccountRoutes::SECTION_DEVICES)->with('status', text('account.devices_revoked'));
    }
}
