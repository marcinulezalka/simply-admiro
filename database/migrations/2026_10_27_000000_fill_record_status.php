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
use Simplysmart\Admiro\Database\RecordStatusBackfill;
use Simplysmart\Admiro\Models\AdmiroAccount;
use Simplysmart\Admiro\Models\Dictionaries\Country;
use Simplysmart\Admiro\Models\Dictionaries\Currency;
use Simplysmart\Admiro\Models\Dictionaries\ExchangeRate;
use Simplysmart\Admiro\Models\Dictionaries\Holiday;
use Simplysmart\Admiro\Models\Dictionaries\Language;
use Simplysmart\Admiro\Models\Dictionaries\TaxRate;
use Simplysmart\Admiro\Models\LegalDocument;
use Simplysmart\Admiro\Models\Organization;
use Simplysmart\Admiro\Models\Permissions\Permission;
use Simplysmart\Admiro\Models\Permissions\Role;

/**
 * Standardowy stan wiersza (Enums\RecordStatus): pusty `status` w tabelach rdzenia (słowniki, organizacje, polityki,
 * role i uprawnienia, konta administratorów) staje się aktywny. Zapis przez modele (Database\RecordStatusBackfill),
 * więc `checksum` i historia zostają spójne. Cofnięcie nie czyści stanu (wiersze zostają aktywne).
 */
return new class extends Migration {
    public function up(): void
    {
        app(RecordStatusBackfill::class)->fill([
            Language::class,
            Country::class,
            Currency::class,
            TaxRate::class,
            ExchangeRate::class,
            Holiday::class,
            Organization::class,
            LegalDocument::class,
            Role::class,
            Permission::class,
            AdmiroAccount::class,
        ]);
    }

    public function down(): void
    {
        // Stan aktywny jest prawidłowym stanem wiersza, więc cofnięcie migracji go nie czyści.
    }
};
