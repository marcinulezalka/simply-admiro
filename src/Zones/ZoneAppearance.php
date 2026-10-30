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

namespace Simplysmart\Admiro\Zones;

use Simplysmart\Admiro\Enums\AppearanceSetting;

/**
 * Wygląd strefy (`appearance` w configu strefy): czy wybory wyglądu zalogowanego konta zapisuje konto
 * (Profile\AccountAppearance — na każdym urządzeniu) zamiast przeglądarki, i domyślny wygląd aplikacji, który
 * konto nadpisuje swoimi wyborami. Domyślne obowiązują tylko przy zapisie w koncie.
 */
final readonly class ZoneAppearance
{
    /**
     * @param bool $account Wybory zapisuje konto (`appearance.account`); false = przeglądarka (js/theme.js).
     * @param array<value-of<AppearanceSetting>, string> $defaults Domyślny wygląd aplikacji (`appearance.defaults`:
     *                                                            ustawienie → wartość z AppearanceSetting::values());
     *                                                            brak ustawienia = wartość bazowa.
     */
    public function __construct(
        public bool $account = false,
        public array $defaults = [],
    ) {}

    /**
     * Domyślna wartość ustawienia w strefie.
     */
    public function default(AppearanceSetting $setting): string
    {
        return $this->defaults[$setting->value] ?? $setting->base();
    }
}
