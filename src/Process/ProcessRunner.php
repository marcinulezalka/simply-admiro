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

namespace Simplysmart\Tools\Process;

/**
 * Uruchamia polecenia systemowe (git, scp, ssh) w osobnym procesie, bez powłoki: argumenty idą tablicą.
 */
interface ProcessRunner
{
    /**
     * Uruchamia polecenie ze strumieniami wyjścia bieżącego procesu.
     *
     * @param  list<string>  $command  Program i argumenty.
     * @param  string|null  $input  Treść podana na standardowe wejście; null — wejście bieżącego procesu.
     * @param  string|null  $directory  Katalog roboczy; null — bieżący.
     * @return int Kod wyjścia polecenia.
     *
     * @throws ProcessException Gdy procesu nie da się uruchomić.
     */
    public function run(array $command, ?string $input = null, ?string $directory = null): int;

    /**
     * Uruchamia polecenie i zwraca jego wyjście.
     *
     * @param  list<string>  $command  Program i argumenty.
     * @return string|null Wyjście bez końcowych białych znaków albo null, gdy polecenie się nie powiodło.
     */
    public function capture(array $command, ?string $directory = null): ?string;
}
