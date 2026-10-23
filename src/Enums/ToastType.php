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

namespace Simplysmart\Mainboard\Enums;

/**
 * Typ toastu (alertu aplikacji): znaczenie, kolor stanu (`--ss-color-<typ>`), ikona i sposób ogłaszania. Kolor nigdy
 * nie jest jedynym nośnikiem znaczenia: toast ma też ikonę i nazwę typu (widoczną albo dla czytników ekranu).
 */
enum ToastType: string
{
    /** Akcja się udała (zapis, wysłanie). */
    case Success = 'success';

    /** Informacja bez potrzeby działania. */
    case Info = 'info';

    /** Coś wymaga uwagi, ale akcja się wykonała. */
    case Warning = 'warning';

    /** Błąd: akcja się nie wykonała; toast nie znika sam. */
    case Danger = 'danger';

    public function icon(): string
    {
        return match ($this) {
            self::Success => 'check-circle',
            self::Info => 'info',
            self::Warning => 'alert-triangle',
            self::Danger => 'alert-circle',
        };
    }

    public function label(): string
    {
        return text('layout.toast.' . $this->value);
    }

    /**
     * Rola ARIA: błąd przerywa czytnik ekranu (`alert`), pozostałe czekają (`status`).
     */
    public function role(): string
    {
        return $this === self::Danger ? 'alert' : 'status';
    }

    /**
     * Czy toast znika sam po chwili (błąd zostaje do zamknięcia).
     */
    public function dismissesItself(): bool
    {
        return $this !== self::Danger;
    }
}
