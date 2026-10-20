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

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Kod logowania; wysyłany z jobu IssueSignInCode (już w tle). Kodu nie ma w temacie,
 * żeby nie był widoczny w powiadomieniach na zablokowanym ekranie.
 */
final class LoginCodeMail extends Mailable
{
    public function __construct(
        public readonly string $accountName,
        #[\SensitiveParameter]
        public readonly string $code,
        public readonly int $validForMinutes,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: text_email('login_code.subject'));
    }

    public function content(): Content
    {
        return new Content(text: 'mainboard::mail.login-code');
    }
}
