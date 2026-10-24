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

/**
 * Potwierdzenie zmiany PIN-u dla właściciela konta (ostrzega, jeśli to nie on).
 */
final class PinChangedMail extends ZoneMailable implements ShouldQueue, ShouldBeEncrypted
{
    use Queueable;

    public function __construct(
        string $zoneName,
        ?int $organizationId,
        public readonly string $accountName,
        public readonly CarbonInterface $changedAt,
    ) {
        parent::__construct($zoneName, $organizationId);
    }

    protected function template(): string
    {
        return 'pin-changed';
    }

    protected function subjectKey(): string
    {
        return 'pin_changed.subject';
    }
}
