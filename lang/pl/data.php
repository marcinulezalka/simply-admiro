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

/*
| Etykiety kolumn dla text_data($model, $kolumna): najpierw "<model>.<kolumna>", potem wspólne "<kolumna>".
*/

return [

    'id' => 'ID',
    'token' => 'Token',
    'parent_id' => 'Nadrzędny',
    'name' => 'Nazwa',
    'status' => 'Status',
    'is_active' => 'Aktywny',
    'sort_order' => 'Kolejność',
    'json_attributes' => 'Atrybuty dodatkowe',
    'json_default_settings' => 'Ustawienia domyślne',
    'json_tags' => 'Tagi',
    'json_i18n' => 'Tłumaczenia',
    'json_migrations' => 'Dane integracji',
    'checksum' => 'Suma kontrolna',
    'json_archive' => 'Archiwum',
    'created_at' => 'Utworzono',
    'updated_at' => 'Zmieniono',
    'deleted_at' => 'Usunięto',
    'code' => 'Kod',
    'is_default' => 'Domyślny',

    'currency' => [
        'name' => 'Nazwa waluty',
        'alpha_code_3' => 'Kod waluty (ISO 4217)',
        'numeric_code' => 'Kod numeryczny (ISO 4217)',
        'decimals' => 'Miejsca po przecinku',
        'is_default' => 'Waluta aplikacji',
    ],

    'language' => [
        'name' => 'Nazwa języka',
        'code' => 'Kod języka (ISO 639)',
        'native_name' => 'Nazwa własna',
        'is_default' => 'Język aplikacji',
        'is_interface' => 'Język interfejsu',
        'flag_icon' => 'Ikona flagi',
    ],

    'country' => [
        'name' => 'Nazwa kraju',
        'alpha_code_2' => 'Kod kraju (ISO 3166-1 alpha-2)',
        'alpha_code_3' => 'Kod kraju (ISO 3166-1 alpha-3)',
        'numeric_code' => 'Kod numeryczny (ISO 3166-1)',
        'dial_code' => 'Numer kierunkowy',
        'currency_id' => 'Waluta',
        'is_eu' => 'Członek UE',
        'is_default' => 'Kraj aplikacji',
        'flag_icon' => 'Ikona flagi',
        'continent' => 'Kontynent',
        'region_code' => 'Region (UN M49)',
        'population' => 'Ludność',
    ],

    'country_language' => [
        'country_id' => 'Kraj',
        'language_id' => 'Język',
        'is_primary' => 'Język główny',
    ],

    'tax_rate' => [
        'name' => 'Stawka',
        'country_id' => 'Kraj',
        'type' => 'Rodzaj stawki',
        'rate' => 'Stawka (%)',
        'valid_from' => 'Obowiązuje od',
        'valid_to' => 'Obowiązuje do',
        'note' => 'Uwagi',
    ],

    'organization' => [
        'short_name' => 'Nazwa skrócona',
        'name' => 'Nazwa organizacji',
        'parent_id' => 'Organizacja nadrzędna',
        'nip' => 'NIP',
        'regon' => 'REGON',
        'krs' => 'KRS',
        'street' => 'Ulica i numer',
        'postal_code' => 'Kod pocztowy',
        'city' => 'Miejscowość',
        'phone' => 'Telefon',
        'email' => 'E-mail',
        'billing_email' => 'E-mail do rozliczeń',
    ],

    'role' => [
        'name' => 'Nazwa roli',
        'guard_name' => 'Strefa (guard)',
        'organization_id' => 'Organizacja (rola własna)',
    ],

    'permission' => [
        'name' => 'Nazwa uprawnienia',
        'guard_name' => 'Strefa (guard)',
    ],

    'permission_denial' => [
        'permission_id' => 'Uprawnienie',
        'model_type' => 'Rodzaj konta',
        'model_id' => 'Konto',
        'organization_id' => 'Organizacja',
    ],

    'audit_event' => [
        'occurred_at' => 'Czas',
        'event' => 'Zdarzenie',
        'level' => 'Waga',
        'zone' => 'Strefa',
        'account_id' => 'Konto',
        'actor_zone' => 'Strefa wykonującego',
        'actor_id' => 'Wykonujący',
        'organization_id' => 'Organizacja',
        'ip' => 'Adres IP',
        'context' => 'Szczegóły',
    ],

    'exchange_rate' => [
        'name' => 'Kurs',
        'currency_id' => 'Waluta',
        'rate_date' => 'Dzień kursu',
        'rate' => 'Kurs (PLN za 1 jednostkę)',
        'source' => 'Źródło',
        'table_number' => 'Numer tabeli NBP',
    ],

    'cookie_policy' => [
        'name' => 'Wersja polityki',
        'version' => 'Numer wersji',
        'published_at' => 'Opublikowano',
        'json_i18n' => 'Tytuł i treść w językach',
    ],

    'cookie_consent' => [
        'recorded_at' => 'Zapisano',
        'consent_id' => 'Identyfikator zgody',
        'policy_version' => 'Wersja polityki',
        'decision' => 'Decyzja',
        'categories' => 'Kategorie',
    ],

    'holiday' => [
        'name' => 'Nazwa',
        'country_id' => 'Kraj',
        'holiday_date' => 'Dzień',
        'source' => 'Źródło',
        'is_day_off' => 'Dzień wolny od pracy',
    ],

];
