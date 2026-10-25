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
use Illuminate\Support\Carbon;
use Simplysmart\Admiro\Auth\Events\PinChanged;
use Simplysmart\Admiro\Contracts\ZoneRegistry;
use Simplysmart\Admiro\Mail\MailOrganization;
use Simplysmart\Admiro\Mail\PinChangedMail;

/**
 * Potwierdzenie zmiany PIN-u dla właściciela konta (zdarzenie PinChanged, e-mail w kolejce).
 */
final readonly class SendPinChangedMail
{
    public function __construct(
        private Mailer $mailer,
        private ZoneRegistry $zones,
        private MailOrganization $organizations,
    ) {}

    public function handle(PinChanged $event): void
    {
        $this->mailer->to($event->account->emailAddress())->queue(new PinChangedMail($event->zone, $this->organizations->for($this->zones->get($event->zone), $event->account), $event->account->displayName(), Carbon::now()));
    }
}
