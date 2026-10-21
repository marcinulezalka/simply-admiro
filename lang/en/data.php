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
| Column labels for text_data($model, $column): "<model>.<column>" first, then the shared "<column>".
*/

return [

    'id' => 'ID',
    'token' => 'Token',
    'parent_id' => 'Parent',
    'name' => 'Name',
    'status' => 'Status',
    'is_active' => 'Active',
    'sort_order' => 'Order',
    'json_attributes' => 'Additional attributes',
    'json_i18n' => 'Translations',
    'json_migrations' => 'Integration data',
    'checksum' => 'Checksum',
    'json_archive' => 'Archive',
    'created_at' => 'Created',
    'updated_at' => 'Updated',
    'deleted_at' => 'Deleted',
    'code' => 'Code',
    'is_default' => 'Default',

    'currency' => [
        'name' => 'Currency name',
        'code' => 'Currency code (ISO 4217)',
        'decimals' => 'Decimal places',
        'is_default' => 'Application currency',
    ],

    'language' => [
        'name' => 'Language name',
        'code' => 'Language code (ISO 639)',
        'native_name' => 'Native name',
        'is_default' => 'Application language',
        'is_interface' => 'Interface language',
    ],

    'country' => [
        'name' => 'Country name',
        'code' => 'Country code (ISO 3166-1)',
        'dial_code' => 'Dialling code',
        'currency_id' => 'Currency',
        'is_eu' => 'EU member',
        'is_default' => 'Application country',
    ],

    'country_language' => [
        'country_id' => 'Country',
        'language_id' => 'Language',
        'is_primary' => 'Primary language',
    ],

    'tax_rate' => [
        'name' => 'Rate',
        'country_id' => 'Country',
        'type' => 'Rate type',
        'rate' => 'Rate (%)',
        'valid_from' => 'Valid from',
        'valid_to' => 'Valid to',
        'note' => 'Note',
    ],

];
