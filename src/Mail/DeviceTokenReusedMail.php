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

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Ostrzeżenie: użyto skopiowanego ciasteczka zaufanego urządzenia, więc wylogowaliśmy wszystkie sesje
 * i odwołaliśmy wszystkie urządzenia konta.
 */
final class DeviceTokenReusedMail extends Mailable implements ShouldQueue, ShouldBeEncrypted
{
    use Queueable;

    public function __construct(
        public readonly string $accountName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: text_email('device_token_reused.subject'));
    }

    public function content(): Content
    {
        return new Content(text: 'mainboard::mail.device-token-reused');
    }
}
