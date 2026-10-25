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

namespace Simplysmart\Admiro\Console;

use Illuminate\Console\Command;
use Simplysmart\Admiro\Security\Honeypot\IpBans;

/**
 * Zdejmuje ban pułapki na boty z adresu IP (bany nie mają listy: w cache jest tylko hash adresu).
 */
final class UnbanIp extends Command
{
    protected $signature = 'admiro:honeypot:unban {ip : Adres IP}';

    protected $description = 'Zdejmuje ban pułapki na boty z adresu IP';

    public function handle(IpBans $bans): int
    {
        $ip = $this->argument('ip');

        if (! is_string($ip) || filter_var($ip, FILTER_VALIDATE_IP) === false) {
            $this->error('Niepoprawny adres IP.');

            return self::FAILURE;
        }

        $bans->unban($ip) ? $this->info("Ban zdjęty: {$ip}") : $this->warn("Adres nie był zbanowany: {$ip}");

        return self::SUCCESS;
    }
}
