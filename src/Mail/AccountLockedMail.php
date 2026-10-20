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

namespace Simplysmart\Mainboard\Mail;

use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Powiadomienie właściciela konta o blokadzie po błędnych PIN-ach (tylko on się o niej dowiaduje).
 */
final class AccountLockedMail extends Mailable implements ShouldQueue, ShouldBeEncrypted
{
    use Queueable;

    public function __construct(
        public readonly string $accountName,
        public readonly CarbonInterface $lockedUntil,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: text_email('account_locked.subject'));
    }

    public function content(): Content
    {
        return new Content(text: 'mainboard::mail.account-locked');
    }
}
