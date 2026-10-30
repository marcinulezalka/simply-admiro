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

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Simplysmart\Admiro\Contracts\CurrentOrganization;
use Simplysmart\Admiro\Contracts\CurrentRole;
use Simplysmart\Admiro\Database\ColumnNames;
use Simplysmart\Admiro\Enums\AppearanceSetting;
use Simplysmart\Admiro\Models\ZoneAccount;
use Simplysmart\Admiro\Zones\ZoneDefinition;

/**
 * Wygląd zapisany w koncie (`json_attributes` pod {@see self::ATTRIBUTE}), w strefie z `appearance.account`
 * (Zones\ZoneAppearance), osobno dla każdej roli konta: klucz to aktywna rola (Contracts\CurrentRole, strefa
 * z `role_contexts`), a bez niej {@see self::WITHOUT_ROLE}. Domyślny wygląd ma aplikacja (`appearance.defaults`),
 * a konto nadpisuje go w danej roli swoimi wyborami (Enums\AppearanceSetting), więc np. właściciel i lekarz w jednym
 * koncie mają osobny wygląd. Wybór spoza wartości strefy (np. kolorystyka usunięta z configu) się nie liczy. Zapis
 * zmienia tylko podane ustawienia bieżącej roli i nie narusza innych atrybutów (historia tabeli zapisuje zmianę).
 */
final readonly class AccountAppearance
{
    public const string ATTRIBUTE = 'appearance';

    /** Klucz wyglądu konta bez aktywnej roli (strefa bez `role_contexts` albo konto bez roli). */
    public const string WITHOUT_ROLE = '*';

    public function __construct(
        private CurrentRole $role,
        private CurrentOrganization $organization,
    ) {}

    /**
     * Bieżący wygląd konta w jego aktywnej roli: każde ustawienie z wyborem konta albo domyślną wartością strefy.
     *
     * @return array<value-of<AppearanceSetting>, string>
     */
    public function of(ZoneDefinition $zone, Model $account): array
    {
        $chosen = $this->chosen($account)[$this->context($account)] ?? null;
        $chosen = is_array($chosen) ? $chosen : [];
        $settings = [];

        foreach (AppearanceSetting::cases() as $setting) {
            $value = $chosen[$setting->value] ?? null;
            $settings[$setting->value] = is_string($value) && in_array($value, $setting->values($zone), true) ? $value : $zone->appearance->default($setting);
        }

        return $settings;
    }

    /**
     * Atrybuty `data-*` na `<html>` rysujące wygląd konta w aktywnej roli (bez wartości bazowych); pusta lista, gdy
     * strefa nie zapisuje wyglądu w koncie albo nie ma zalogowanego konta.
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
     * Zapisuje wybory konta w jego aktywnej roli (ustawienie → wartość, już sprawdzone z AppearanceSetting::values()).
     *
     * @param array<value-of<AppearanceSetting>, string> $choices
     */
    public function save(ZoneAccount $account, array $choices): void
    {
        $context = $this->context($account);
        $chosen = $this->chosen($account);
        $current = $chosen[$context] ?? null;
        $chosen[$context] = [...(is_array($current) ? $current : []), ...$choices];

        $this->store($account, $chosen);
    }

    /**
     * Usuwa wybory konta w jego aktywnej roli: w niej wraca domyślny wygląd aplikacji, inne role zostają.
     */
    public function reset(ZoneAccount $account): void
    {
        $chosen = $this->chosen($account);
        unset($chosen[$this->context($account)]);

        $this->store($account, $chosen);
    }

    /**
     * Klucz wyglądu: aktywna rola konta w organizacji żądania albo {@see self::WITHOUT_ROLE}.
     */
    private function context(Model $account): string
    {
        $role = $account instanceof Authenticatable ? $this->role->of($account, $this->organization->id()) : null;

        return $role ?? self::WITHOUT_ROLE;
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
     * @param array<array-key, mixed> $chosen
     */
    private function store(ZoneAccount $account, array $chosen): void
    {
        $attributes = $account->getAttribute(ColumnNames::JSON_ATTRIBUTES);
        $attributes = is_array($attributes) ? $attributes : [];

        if ($chosen === []) {
            unset($attributes[self::ATTRIBUTE]);
        } else {
            $attributes[self::ATTRIBUTE] = $chosen;
        }

        $account->forceFill([ColumnNames::JSON_ATTRIBUTES => $attributes])->save();
    }
}
