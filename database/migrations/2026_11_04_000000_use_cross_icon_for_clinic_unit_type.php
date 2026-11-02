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
use Simplysmart\Admiro\Models\OrganizationUnitType;

/**
 * Typ jednostki „Poradnia” z ikoną krzyża medycznego (`cross`) zamiast serca — tylko gdy administrator nie zmienił
 * jej w panelu (nadal `heart`); zapis przez model, więc historia ma wpis. Cofnięcie nie zmienia danych.
 */
return new class extends Migration {
    public function up(): void
    {
        OrganizationUnitType::query()->where('code', 'poradnia')->where('icon', 'heart')->each(static function (OrganizationUnitType $type): void {
            $type->forceFill(['icon' => 'cross'])->save();
        });
    }

    public function down(): void {}
};
