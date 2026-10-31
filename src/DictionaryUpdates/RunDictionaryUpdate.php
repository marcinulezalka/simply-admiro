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

namespace Simplysmart\Admiro\DictionaryUpdates;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Simplysmart\Admiro\Queries\DictionaryUpdates\DictionaryUpdateRequestList;

/**
 * Zadanie w kolejce po zatwierdzeniu prośby o aktualizację słownika przez administratora: wywołuje
 * Contracts\DictionaryUpdater słownika i zapisuje wynik w prośbie (DictionaryUpdateRequests::run()). Jedna próba —
 * błąd zostaje w prośbie, a strefa może poprosić ponownie.
 */
final class RunDictionaryUpdate implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    /** Pobranie i zapis dużego słownika może trwać kilka minut. */
    public int $timeout = 1800;

    public function __construct(
        public readonly string $request,
    ) {}

    public function handle(DictionaryUpdateRequestList $requests, DictionaryUpdateRequests $updates): void
    {
        $request = $requests->find($this->request);

        if ($request !== null) {
            $updates->run($request);
        }
    }
}
