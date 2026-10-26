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

namespace Simplysmart\Admiro\Database;

use Illuminate\Database\Eloquent\Model;
use Simplysmart\Admiro\Enums\RecordStatus;

/**
 * Uzupełnia pusty `status` wierszy modeli ze standardowym stanem wiersza (RecordStatus) na aktywny — dla migracji
 * danych paczek. Zapis idzie przez model (także usunięte miękko wiersze), więc `checksum` liczy się od nowa, a trigger
 * zapisuje zmianę w historii; `updated_at` zostaje bez zmian. Modele ze stanem dziedzinowym w `status` pomija.
 */
final class RecordStatusBackfill
{
    /**
     * @param list<class-string<Model>> $models
     */
    public function fill(array $models): void
    {
        foreach ($models as $model) {
            $prototype = new $model();

            if (! method_exists($prototype, 'usesRecordStatus') || ! $prototype->usesRecordStatus()) {
                continue;
            }

            $query = $model::query();
            $query->withoutGlobalScopes();
            $query->whereNull(ColumnNames::STATUS);

            foreach ($query->lazyById(200) as $row) {
                $row->timestamps = false;
                $row->forceFill([ColumnNames::STATUS => RecordStatus::Active])->save();
            }
        }
    }
}
