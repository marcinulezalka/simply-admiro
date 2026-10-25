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

/**
 * Kod logowania; wysyłany z jobu IssueSignInCode (już w tle). Kodu nie ma w temacie,
 * żeby nie był widoczny w powiadomieniach na zablokowanym ekranie.
 */
final class LoginCodeMail extends ZoneMailable
{
    public function __construct(
        string $zoneName,
        ?int $organizationId,
        public readonly string $accountName,
        #[\SensitiveParameter]
        public readonly string $code,
        public readonly int $validForMinutes,
    ) {
        parent::__construct($zoneName, $organizationId);
    }

    protected function template(): string
    {
        return 'login-code';
    }

    protected function subjectKey(): string
    {
        return 'login_code.subject';
    }
}
