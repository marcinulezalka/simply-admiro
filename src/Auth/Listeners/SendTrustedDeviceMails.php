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

namespace Simplysmart\Admiro\Auth\Listeners;

use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Support\Carbon;
use Simplysmart\Admiro\Auth\AccessRevocationReason;
use Simplysmart\Admiro\Auth\Events\AccountAccessRevoked;
use Simplysmart\Admiro\Auth\Events\TrustedDeviceRegistered;
use Simplysmart\Admiro\Contracts\ZoneRegistry;
use Simplysmart\Admiro\Http\AccountRoutes;
use Simplysmart\Admiro\Mail\DeviceTokenReusedMail;
use Simplysmart\Admiro\Mail\MailOrganization;
use Simplysmart\Admiro\Mail\NewTrustedDeviceMail;

/**
 * E-maile do właściciela konta o jego zaufanych urządzeniach: nowe urządzenie i użycie skopiowanego tokenu.
 */
final readonly class SendTrustedDeviceMails
{
    public function __construct(
        private Mailer $mailer,
        private UrlGenerator $url,
        private ZoneRegistry $zones,
        private MailOrganization $organizations,
    ) {}

    public function handle(TrustedDeviceRegistered|AccountAccessRevoked $event): void
    {
        $account = $event->account;
        $zone = $this->zones->get($event->zone);
        $organization = $this->organizations->for($zone, $account);

        if ($event instanceof TrustedDeviceRegistered) {
            $devices = $this->url->route($zone->routeName(AccountRoutes::SECURITY)) . '#' . AccountRoutes::SECTION_DEVICES;
            $this->mailer->to($account->emailAddress())->queue(new NewTrustedDeviceMail($zone->name, $organization, $account->displayName(), $event->deviceName, Carbon::now(), $devices));
        } elseif ($event->reason === AccessRevocationReason::DeviceTokenReused) {
            $this->mailer->to($account->emailAddress())->queue(new DeviceTokenReusedMail($zone->name, $organization, $account->displayName()));
        }
    }
}
