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

namespace Simplysmart\Admiro\Tools\PrivatePackages;

/**
 * Uruchamia `composer update` dla wskazanych paczek z innym plikiem manifestu (zmienna COMPOSER).
 */
interface ComposerRunner
{
    /**
     * @param  string  $root  Katalog projektu.
     * @param  string  $manifest  Nazwa pliku manifestu względem $root (np. composer.private.json).
     * @param  list<string>  $packages  Paczki do aktualizacji.
     * @param  list<string>  $options  Dodatkowe opcje composera (np. `--no-install`).
     * @return int Kod wyjścia composera.
     */
    public function update(string $root, string $manifest, array $packages, array $options = []): int;
}
