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

namespace Simplysmart\Admiro\Auth\SignIn;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Simplysmart\Admiro\Contracts\AccountLocator;
use Simplysmart\Admiro\Contracts\CurrentZone;
use Simplysmart\Admiro\Contracts\ZoneRegistry;
use Simplysmart\Admiro\Mail\LoginCodeMail;
use Simplysmart\Admiro\Mail\MailOrganization;
use Simplysmart\Admiro\Zones\ZoneDefinition;

/**
 * Wydaje i wysyła kod logowania w tle. Żądanie kolejkuje ten job zawsze, także dla nieznanego
 * adresu, więc odpowiedź trwa tyle samo i nie zdradza, czy konto istnieje (ładunek szyfrowany).
 * Na kolejce `sync` przywraca strefę żądania, które go uruchomiło.
 */
final class IssueSignInCode implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use Queueable;

    public function __construct(
        public readonly string $zone,
        #[\SensitiveParameter]
        public readonly string $email,
        public readonly ?string $ip,
        public readonly ?string $userAgent,
        public readonly bool $replaceRecent = false,
    ) {}

    public function handle(ZoneRegistry $zones, CurrentZone $current, AccountLocator $accounts, LoginChallengeService $challenges, Mailer $mailer, MailOrganization $organizations): void
    {
        $current->within($zones->get($this->zone), function (ZoneDefinition $zone) use ($accounts, $challenges, $mailer, $organizations): void {
            $account = $accounts->findByEmail($this->email);

            if ($account === null || ! $account->canSignIn()) {
                return;
            }

            $code = $this->replaceRecent
                ? $challenges->issue($account, $this->ip, $this->userAgent)
                : $challenges->issueUnlessRecent($account, $this->ip, $this->userAgent);

            if ($code !== null) {
                $mailer->to($account->emailAddress())->send(new LoginCodeMail($zone->name, $organizations->for($zone, $account), $account->displayName(), $code, $zone->auth()->codeTtlMinutes));
            }
        });
    }
}
