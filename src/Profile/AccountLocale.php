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

namespace Simplysmart\Mainboard\Profile;

use Illuminate\Database\Eloquent\Model;
use Simplysmart\Mainboard\Contracts\InterfaceLocales;
use Simplysmart\Mainboard\Database\ColumnNames;

/**
 * Język interfejsu wybrany przez konto (`json_attributes` pod {@see self::ATTRIBUTE}): obowiązuje tylko, gdy jest
 * wśród włączonych języków interfejsu (InterfaceLocales); wyłączony albo niewybrany to null (język z adresu).
 */
final readonly class AccountLocale
{
    public const string ATTRIBUTE = 'locale';

    public function __construct(
        private InterfaceLocales $locales,
    ) {}

    public function of(Model $account): ?string
    {
        $attributes = $account->getAttribute(ColumnNames::JSON_ATTRIBUTES);
        $locale = is_array($attributes) ? ($attributes[self::ATTRIBUTE] ?? null) : null;

        return is_string($locale) && $this->locales->isEnabled($locale) ? $locale : null;
    }

    /**
     * Czy konto może wybrać język (włączony więcej niż jeden).
     */
    public function isSelectable(): bool
    {
        return count($this->locales->enabled()) > 1;
    }

    /**
     * @return list<string>
     */
    public function choices(): array
    {
        return $this->locales->enabled();
    }
}
