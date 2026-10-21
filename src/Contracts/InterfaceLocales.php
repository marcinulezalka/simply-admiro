<?php

declare(strict_types=1);

/**
 * This file is part of simplysmart/mainboard.
 *
 * Copyright (c) 2026 Marcin Ulezalka <marcin.ulezalka@simplysmart.pl>
 *
 * Licensed under the Simplysmart Restricted Source License 1.0.
 * See the LICENSE file distributed with this source code.
 */

namespace Simplysmart\Mainboard\Contracts;

/**
 * Języki interfejsu włączone teraz: podzbiór języków tras (APP_LOCALES, Localization\LocaleRouting),
 * który wybiera administrator w słowniku języków.
 */
interface InterfaceLocales
{
    /**
     * @return list<string>
     */
    public function enabled(): array;

    public function default(): string;

    public function isEnabled(string $locale): bool;

    /**
     * Zapomina zapamiętany wybór (po zmianie słownika języków).
     */
    public function forget(): void;
}
