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

namespace Simplysmart\Admiro\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Ostrzeżenie: użyto skopiowanego ciasteczka zaufanego urządzenia, więc wylogowaliśmy wszystkie sesje
 * i odwołaliśmy wszystkie urządzenia konta.
 */
final class DeviceTokenReusedMail extends ZoneMailable implements ShouldQueue, ShouldBeEncrypted
{
    use Queueable;

    public function __construct(
        string $zoneName,
        ?int $organizationId,
        public readonly string $accountName,
    ) {
        parent::__construct($zoneName, $organizationId);
    }

    protected function template(): string
    {
        return 'device-token-reused';
    }

    protected function subjectKey(): string
    {
        return 'device_token_reused.subject';
    }
}
