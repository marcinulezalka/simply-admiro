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
use Simplysmart\Mainboard\Auth\Recovery\SecurityCodePurpose;

/**
 * Kod bezpieczeństwa poza logowaniem (Auth\Recovery\SessionCodes): potwierdzenie zapasowego adresu albo reset PIN-u;
 * teksty `security_code.<cel>.*`. W kolejce (ładunek szyfrowany), kodu nie ma w temacie.
 */
final class SecurityCodeMail extends ZoneMailable implements ShouldQueue, ShouldBeEncrypted
{
    use Queueable;

    public function __construct(
        string $zoneName,
        ?int $organizationId,
        public readonly string $accountName,
        #[\SensitiveParameter]
        public readonly string $code,
        public readonly int $validForMinutes,
        public readonly SecurityCodePurpose $purpose,
    ) {
        parent::__construct($zoneName, $organizationId);
    }

    protected function template(): string
    {
        return 'security-code';
    }

    protected function subjectKey(): string
    {
        return 'security_code.' . $this->purpose->value . '.subject';
    }
}
