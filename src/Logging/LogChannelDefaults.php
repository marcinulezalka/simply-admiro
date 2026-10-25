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

namespace Simplysmart\Admiro\Logging;

use Illuminate\Contracts\Config\Repository;

/**
 * Tworzy dzienny kanał logów Core (np. dziennik logowań, honeypot), gdy aplikacja nie zdefiniowała kanału
 * o nazwie z configu Core; plik `storage/<plik>-RRRR-MM-DD.log`, przechowywany tyle dni co logi poczty.
 */
final readonly class LogChannelDefaults
{
    public function __construct(
        private Repository $config,
        private string $storagePath,
    ) {}

    /**
     * @param string $channelKey Klucz configu Core z nazwą kanału (np. `admiro.auth.log_channel`).
     * @param string $file Ścieżka pliku względem storage (np. `logs/sign-in.log`).
     */
    public function define(string $channelKey, string $file): void
    {
        $channel = $this->config->get($channelKey);

        if (! is_string($channel) || $channel === '' || $this->config->has("logging.channels.{$channel}")) {
            return;
        }

        $this->config->set("logging.channels.{$channel}", [
            'driver' => 'daily',
            'path' => $this->storagePath . '/' . $file,
            'level' => 'info',
            'days' => $this->config->get('admiro.mail.log_days'),
            'replace_placeholders' => true,
        ]);
    }
}
