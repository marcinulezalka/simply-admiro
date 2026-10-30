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

namespace Simplysmart\Admiro\Profile;

use Illuminate\Database\Eloquent\Model;
use Simplysmart\Admiro\Database\ColumnNames;
use Simplysmart\Admiro\Enums\AppearanceSetting;
use Simplysmart\Admiro\Models\ZoneAccount;
use Simplysmart\Admiro\Zones\ZoneDefinition;

/**
 * Wygląd zapisany w koncie (`json_attributes` pod {@see self::ATTRIBUTE}), w strefie z `appearance.account`
 * (Zones\ZoneAppearance): domyślny wygląd ma aplikacja (`appearance.defaults`), a konto nadpisuje go swoimi
 * wyborami (Enums\AppearanceSetting). Wybór spoza wartości strefy (np. kolorystyka usunięta z configu) się nie liczy.
 * Zapis zmienia tylko podane ustawienia i nie narusza innych atrybutów (historia tabeli zapisuje zmianę jak każdą inną).
 */
final class AccountAppearance
{
    public const string ATTRIBUTE = 'appearance';

    /**
     * Bieżący wygląd konta: każde ustawienie z wyborem konta albo domyślną wartością strefy.
     *
     * @return array<value-of<AppearanceSetting>, string>
     */
    public function of(ZoneDefinition $zone, Model $account): array
    {
        $chosen = $this->chosen($account);
        $settings = [];

        foreach (AppearanceSetting::cases() as $setting) {
            $value = $chosen[$setting->value] ?? null;
            $settings[$setting->value] = is_string($value) && in_array($value, $setting->values($zone), true) ? $value : $zone->appearance->default($setting);
        }

        return $settings;
    }

    /**
     * Atrybuty `data-*` na `<html>` rysujące wygląd konta (bez wartości bazowych); pusta lista, gdy strefa nie zapisuje
     * wyglądu w koncie albo nie ma zalogowanego konta.
     *
     * @return array<string, string>
     */
    public function htmlAttributes(ZoneDefinition $zone, ?Model $account): array
    {
        if (! $zone->appearance->account || $account === null) {
            return [];
        }

        $attributes = [];

        foreach ($this->of($zone, $account) as $key => $value) {
            $setting = AppearanceSetting::from($key);

            if ($value !== $setting->base()) {
                $attributes[$setting->attribute()] = $value;
            }
        }

        return $attributes;
    }

    /**
     * Zapisuje wybory konta (ustawienie → wartość, już sprawdzone z AppearanceSetting::values()).
     *
     * @param array<value-of<AppearanceSetting>, string> $choices
     */
    public function save(ZoneAccount $account, array $choices): void
    {
        $this->store($account, [...$this->chosen($account), ...$choices]);
    }

    /**
     * Usuwa wybory konta: wraca domyślny wygląd aplikacji.
     */
    public function reset(ZoneAccount $account): void
    {
        $this->store($account, []);
    }

    /**
     * @return array<array-key, mixed>
     */
    private function chosen(Model $account): array
    {
        $attributes = $account->getAttribute(ColumnNames::JSON_ATTRIBUTES);
        $chosen = is_array($attributes) ? ($attributes[self::ATTRIBUTE] ?? null) : null;

        return is_array($chosen) ? $chosen : [];
    }

    /**
     * @param array<array-key, mixed> $choices
     */
    private function store(ZoneAccount $account, array $choices): void
    {
        $attributes = $account->getAttribute(ColumnNames::JSON_ATTRIBUTES);
        $attributes = is_array($attributes) ? $attributes : [];

        if ($choices === []) {
            unset($attributes[self::ATTRIBUTE]);
        } else {
            $attributes[self::ATTRIBUTE] = $choices;
        }

        $account->forceFill([ColumnNames::JSON_ATTRIBUTES => $attributes])->save();
    }
}
