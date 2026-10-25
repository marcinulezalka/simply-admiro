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

namespace Simplysmart\Mainboard\Console;

use Illuminate\Config\Repository;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Simplysmart\Mainboard\Models\CookieConsent;

/**
 * Usuwa z rejestru zgody na cookies starsze niż `mainboard.cookies.retention_days` dni; uruchamiana codziennie.
 */
final class PruneCookieConsents extends Command
{
    protected $signature = 'mainboard:cookies:prune';

    protected $description = 'Usuwa stare wpisy rejestru zgód na cookies';

    public function handle(Repository $config): int
    {
        $before = Carbon::now()->subDays($config->integer('mainboard.cookies.retention_days'));
        $deleted = CookieConsent::query()->toBase()->where('recorded_at', '<', $before)->delete();
        $this->info("Usunięto zgód: {$deleted}");

        return self::SUCCESS;
    }
}
