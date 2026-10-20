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

namespace Simplysmart\Mainboard\Auth;

use Illuminate\Contracts\Config\Repository;

/**
 * Tworzy dzienny kanał dziennika logowań (storage/logs/sign-in-RRRR-MM-DD.log), gdy aplikacja
 * nie zdefiniowała kanału o nazwie z `mainboard.auth.log_channel`.
 */
final readonly class SignInLogDefaults
{
    private const string LOG_FILE = 'logs/sign-in.log';

    public function __construct(
        private Repository $config,
        private string $storagePath,
    ) {}

    public function apply(): void
    {
        $channel = $this->config->get('mainboard.auth.log_channel');

        if (! is_string($channel) || $channel === '' || $this->config->has("logging.channels.{$channel}")) {
            return;
        }

        $this->config->set("logging.channels.{$channel}", [
            'driver' => 'daily',
            'path' => $this->storagePath . '/' . self::LOG_FILE,
            'level' => 'info',
            'days' => $this->config->get('mainboard.mail.log_days'),
            'replace_placeholders' => true,
        ]);
    }
}
