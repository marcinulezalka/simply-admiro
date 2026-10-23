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

namespace Simplysmart\Mainboard\Auth\Listeners;

use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Support\Carbon;
use Simplysmart\Mainboard\Auth\AccessRevocationReason;
use Simplysmart\Mainboard\Auth\Events\AccountAccessRevoked;
use Simplysmart\Mainboard\Auth\Events\TrustedDeviceRegistered;
use Simplysmart\Mainboard\Contracts\ZoneRegistry;
use Simplysmart\Mainboard\Http\AccountRoutes;
use Simplysmart\Mainboard\Mail\DeviceTokenReusedMail;
use Simplysmart\Mainboard\Mail\NewTrustedDeviceMail;

/**
 * E-maile do właściciela konta o jego zaufanych urządzeniach: nowe urządzenie i użycie skopiowanego tokenu.
 */
final readonly class SendTrustedDeviceMails
{
    public function __construct(
        private Mailer $mailer,
        private UrlGenerator $url,
        private ZoneRegistry $zones,
    ) {}

    public function handle(TrustedDeviceRegistered|AccountAccessRevoked $event): void
    {
        $account = $event->account;

        if ($event instanceof TrustedDeviceRegistered) {
            $devices = $this->url->route($this->zones->get($event->zone)->routeName(AccountRoutes::SECURITY)) . '#' . AccountRoutes::SECTION_DEVICES;
            $this->mailer->to($account->emailAddress())->queue(new NewTrustedDeviceMail($account->displayName(), $event->deviceName, Carbon::now(), $devices));
        } elseif ($event->reason === AccessRevocationReason::DeviceTokenReused) {
            $this->mailer->to($account->emailAddress())->queue(new DeviceTokenReusedMail($account->displayName()));
        }
    }
}
