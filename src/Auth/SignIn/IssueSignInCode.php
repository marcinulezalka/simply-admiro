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

namespace Simplysmart\Mainboard\Auth\SignIn;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Simplysmart\Mainboard\Contracts\AccountLocator;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Contracts\ZoneRegistry;
use Simplysmart\Mainboard\Mail\LoginCodeMail;

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

    public function handle(ZoneRegistry $zones, CurrentZone $current, AccountLocator $accounts, LoginChallengeService $challenges, Mailer $mailer): void
    {
        $zone = $zones->get($this->zone);
        $previous = $current->has() ? $current->get() : null;
        $current->set($zone);

        try {
            $account = $accounts->findByEmail($this->email);

            if ($account === null || ! $account->canSignIn()) {
                return;
            }

            $code = $this->replaceRecent
                ? $challenges->issue($account, $this->ip, $this->userAgent)
                : $challenges->issueUnlessRecent($account, $this->ip, $this->userAgent);

            if ($code !== null) {
                $mailer->to($account->emailAddress())->send(new LoginCodeMail($account->displayName(), $code, $zone->auth()->codeTtlMinutes));
            }
        } finally {
            $previous === null ? $current->forget() : $current->set($previous);
        }
    }
}
