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
use Simplysmart\Admiro\DictionaryUpdates\DictionaryUpdaters;

/**
 * Aktualizacje słowników po zatwierdzeniu administratora: rejestr słowników paczek (DictionaryUpdaters, singleton).
 */
final class DictionaryUpdatesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DictionaryUpdaters::class);
    }
}
