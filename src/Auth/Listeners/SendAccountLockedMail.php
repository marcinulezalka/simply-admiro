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
use Simplysmart\Admiro\Auth\Events\AccountLocked;
use Simplysmart\Admiro\Contracts\ZoneRegistry;
use Simplysmart\Admiro\Mail\AccountLockedMail;
use Simplysmart\Admiro\Mail\MailOrganization;

/**
 * Powiadomienie właściciela o blokadzie konta po błędnych PIN-ach (zdarzenie AccountLocked, e-mail w kolejce).
 */
final readonly class SendAccountLockedMail
{
    public function __construct(
        private Mailer $mailer,
        private ZoneRegistry $zones,
        private MailOrganization $organizations,
    ) {}

    public function handle(AccountLocked $event): void
    {
        $this->mailer->to($event->account->emailAddress())->queue(new AccountLockedMail($event->zone, $this->organizations->for($this->zones->get($event->zone), $event->account), $event->account->displayName(), $event->until));
    }
}
