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

namespace Simplysmart\Admiro\Enums;

use Simplysmart\Admiro\Contracts\HasLabel;

/**
 * Komenda systemu wpisywana w polu komend na dole menu błyskawicy (admiro::search.commands, ikona „/”): wybrana
 * komenda (domyślnie pierwsza — `todo:`) i tekst pola trafiają do Http\Controllers\Account\TaskController. Prefiks
 * w polu to `<value>:`, etykieta i opis w `enums.quick_command.<value>`.
 */
enum QuickCommand: string implements HasLabel
{
    case Todo = 'todo';

    /**
     * Komenda zaznaczona, zanim konto wybierze inną.
     */
    public static function default(): self
    {
        return self::Todo;
    }

    /**
     * Prefiks komendy w polu (np. `todo:`).
     */
    public function prefix(): string
    {
        return $this->value . ':';
    }

    public function icon(): string
    {
        return match ($this) {
            self::Todo => 'check',
        };
    }

    public function label(): string
    {
        return text('enums.quick_command.' . $this->value . '.label');
    }

    /**
     * Podpowiedź pola dla wybranej komendy (np. „Nazwa zadania”).
     */
    public function placeholder(): string
    {
        return text('enums.quick_command.' . $this->value . '.placeholder');
    }
}
