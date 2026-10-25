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

namespace Simplysmart\Mainboard\Localization;

use Illuminate\Database\ConnectionInterface;
use Simplysmart\Mainboard\Contracts\InterfaceLocales;
use Simplysmart\Mainboard\Database\ColumnNames;
use Simplysmart\Mainboard\Models\DataModel;
use Simplysmart\Mainboard\Models\Dictionaries\Country;
use Simplysmart\Mainboard\Models\Dictionaries\Currency;
use Simplysmart\Mainboard\Models\Dictionaries\Language;

/**
 * Ustawienia lokalizacji aplikacji w słownikach: kraj, język i waluta domyślne (`is_default`, zawsze jeden wpis)
 * oraz języki interfejsu (`is_interface`) w granicach `APP_LOCALES`; język domyślny musi być językiem interfejsu.
 * Po zapisie odświeża języki interfejsu (cache).
 */
final readonly class LocalizationSettings
{
    public function __construct(
        private ConnectionInterface $db,
        private LocaleRouting $routing,
        private InterfaceLocales $locales,
    ) {}

    /**
     * @param list<string> $interfaceCodes Kody języków interfejsu.
     *
     * @throws LocalizationSettingsRejected
     */
    public function update(Country $country, Language $language, Currency $currency, array $interfaceCodes): void
    {
        $ceiling = $this->routing->enabled();
        $interfaceCodes = array_values(array_unique($interfaceCodes));

        if ($interfaceCodes === [] || array_diff($interfaceCodes, $ceiling) !== []) {
            throw LocalizationSettingsRejected::because('interface languages must be a non-empty subset of APP_LOCALES.');
        }

        if (! in_array($language->code, $interfaceCodes, true)) {
            throw LocalizationSettingsRejected::because('the default language must be an interface language.');
        }

        $this->db->transaction(function () use ($country, $language, $currency, $ceiling, $interfaceCodes): void {
            $this->makeDefault($country);
            $this->makeDefault($language);
            $this->makeDefault($currency);

            $candidates = Language::query();
            $candidates->whereIn(Language::CODE, $ceiling);

            foreach ($candidates->get() as $candidate) {
                $candidate->forceFill(['is_interface' => in_array($candidate->code, $interfaceCodes, true)])->save();
            }
        });

        $this->locales->forget();
    }

    /**
     * Jedyny wpis domyślny w słowniku (zapis przez model: historia i checksum).
     */
    private function makeDefault(DataModel $entry): void
    {
        $query = $entry->newQuery();
        $query->where(ColumnNames::IS_DEFAULT, true);
        $query->whereKeyNot($entry->getKey());

        foreach ($query->get() as $previous) {
            $previous->forceFill([ColumnNames::IS_DEFAULT => false])->save();
        }

        $entry->forceFill([ColumnNames::IS_DEFAULT => true])->save();
    }
}
