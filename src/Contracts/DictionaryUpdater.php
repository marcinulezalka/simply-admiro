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

namespace Simplysmart\Admiro\Contracts;

use RuntimeException;

/**
 * Słownik paczki, który administrator aktualizuje ze źródła zewnętrznego po zatwierdzeniu prośby
 * (DictionaryUpdates\DictionaryUpdateRequests): strefy nigdy nie pobierają danych z zewnątrz same — prosią
 * o aktualizację, a po zatwierdzeniu zadanie w kolejce (DictionaryUpdates\RunDictionaryUpdate) wywołuje update().
 * Paczka rejestruje swój słownik w DictionaryUpdates\DictionaryUpdaters.
 */
interface DictionaryUpdater
{
    /**
     * Stały klucz słownika (małe litery i myślniki), zapisywany w prośbach.
     */
    public function key(): string;

    /**
     * Nazwa słownika dla administratora w bieżącym języku.
     */
    public function label(): string;

    /**
     * Pobiera słownik ze źródła i zapisuje go w aplikacji.
     *
     * @return string Krótkie podsumowanie dla administratora (np. liczba wpisów).
     *
     * @throws RuntimeException Gdy źródło jest niedostępne albo dane są niepoprawne (komunikat trafia do prośby).
     */
    public function update(): string;
}
