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

use Illuminate\Database\Migrations\Migration;
use Simplysmart\Admiro\Database\ZoneTables;

/**
 * Zapasowy adres e-mail kont strefy administratorów (Database\ZoneTables::addRecoveryEmail()); nowe strefy dostają
 * te kolumny od razu w ZoneTables::create().
 */
return new class extends Migration {
    private const string ZONE = 'admiro';

    public function up(): void
    {
        app(ZoneTables::class)->addRecoveryEmail(self::ZONE);
    }

    public function down(): void
    {
        app(ZoneTables::class)->dropRecoveryEmail(self::ZONE);
    }
};
