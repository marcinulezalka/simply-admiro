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
use Illuminate\Support\Facades\Schema;
use Simplysmart\Admiro\Database\ZoneTables;
use Simplysmart\Admiro\Zones\ZoneTable;

/**
 * Tabela zaufanych urządzeń strefy `admiro` dla baz utworzonych przed jej wprowadzeniem; nowa instalacja
 * dostaje ją już z `create_admiro_tables` (ZoneTables::create()).
 */
return new class extends Migration {
    public function up(): void
    {
        app(ZoneTables::class)->createTrustedDevices('admiro');
    }

    public function down(): void
    {
        Schema::dropIfExists(ZoneTable::TrustedDevices->for('admiro'));
    }
};
