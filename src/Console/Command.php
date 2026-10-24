<?php

declare(strict_types=1);

/**
 * This file is part of simplysmart/tools.
 *
 * Copyright (c) 2026 Marcin Ulezalka <marcin.ulezalka@simplysmart.pl>
 *
 * Licensed under the Simplysmart Restricted Source License 1.0.
 * See the LICENSE file distributed with this source code.
 */

namespace Simplysmart\Tools\Console;

/**
 * Polecenie `vendor/bin/simplysmart <nazwa>`: nazwa i opis do listy poleceń oraz uruchomienie z argumentami.
 */
interface Command
{
    /**
     * Nazwa w stylu artisan (`deploy:onboarding`).
     */
    public function name(): string;

    /**
     * Jednozdaniowy opis do `simplysmart list`.
     */
    public function description(): string;

    /**
     * @param  list<string>  $arguments  Argumenty bez nazwy polecenia.
     * @param  string  $workingDirectory  Katalog, z którego uruchomiono polecenie.
     * @param  resource  $stdout  Strumień komunikatów.
     * @param  resource  $stderr  Strumień błędów.
     * @return int Kod wyjścia procesu.
     */
    public function run(array $arguments, string $workingDirectory, $stdout, $stderr): int;
}
