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

namespace Simplysmart\Admiro\Providers;

use Illuminate\Support\ServiceProvider;
use Simplysmart\Admiro\Console\SeedLegalDocuments;

/**
 * Dokumenty prawne: polecenie `admiro:legal:seed` (pierwsze wersje polityk). Strony dokumentów to trasy Core
 * (routes/web.php), odnośniki w strefach składa Legal\LegalLinks.
 */
final class LegalServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([SeedLegalDocuments::class]);
        }
    }
}
