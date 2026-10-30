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
    'json_default_settings' => 'Default settings',
    'json_tags' => 'Tags',
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
        'alpha_code_3' => 'Currency code (ISO 4217)',
        'numeric_code' => 'Numeric code (ISO 4217)',
        'decimals' => 'Decimal places',
        'is_default' => 'Application currency',
    ],

    'language' => [
        'name' => 'Language name',
        'code' => 'Language code (ISO 639)',
        'native_name' => 'Native name',
        'is_default' => 'Application language',
        'is_interface' => 'Interface language',
        'flag_icon' => 'Flag icon',
    ],

    'country' => [
        'name' => 'Country name',
        'alpha_code_2' => 'Country code (ISO 3166-1 alpha-2)',
        'alpha_code_3' => 'Country code (ISO 3166-1 alpha-3)',
        'numeric_code' => 'Numeric code (ISO 3166-1)',
        'dial_code' => 'Dialling code',
        'currency_id' => 'Currency',
        'is_eu' => 'EU member',
        'is_default' => 'Application country',
        'flag_icon' => 'Flag icon',
        'continent' => 'Continent',
        'region_code' => 'Region (UN M49)',
        'population' => 'Population',
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

    'organization' => [
        'short_name' => 'Short name',
        'name' => 'Organization name',
        'parent_id' => 'Parent organization',
        'is_department' => 'Department',
        'nip' => 'Tax ID (NIP)',
        'regon' => 'REGON',
        'krs' => 'KRS',
        'street' => 'Street and number',
        'postal_code' => 'Postal code',
        'city' => 'City',
        'country_code' => 'Country',
        'phone' => 'Landline phone',
        'mobile_phone' => 'Mobile phone',
        'email' => 'E-mail',
        'billing_email' => 'Billing e-mail',
    ],

    'role' => [
        'name' => 'Role name',
        'guard_name' => 'Zone (guard)',
        'organization_id' => 'Organization (own role)',
    ],

    'permission' => [
        'name' => 'Permission name',
        'guard_name' => 'Zone (guard)',
    ],

    'permission_denial' => [
        'permission_id' => 'Permission',
        'model_type' => 'Account type',
        'model_id' => 'Account',
        'organization_id' => 'Organization',
    ],

    'audit_event' => [
        'occurred_at' => 'Time',
        'event' => 'Event',
        'level' => 'Level',
        'zone' => 'Zone',
        'account_id' => 'Account',
        'actor_zone' => 'Actor zone',
        'actor_id' => 'Actor',
        'organization_id' => 'Organization',
        'ip' => 'IP address',
        'context' => 'Details',
    ],

    'exchange_rate' => [
        'name' => 'Rate',
        'currency_id' => 'Currency',
        'rate_date' => 'Rate date',
        'rate' => 'Rate (PLN per 1 unit)',
        'source' => 'Source',
        'table_number' => 'NBP table number',
    ],

    'account_task' => [
        'name' => 'Name',
        'status' => 'Status',
        'zone' => 'Zone',
        'account_id' => 'Account',
        'issue' => 'Issue number',
        'title' => 'Title',
        'description' => 'Description',
        'due_on' => 'Due date',
        'delay_days' => 'Delay (days)',
        'project' => 'Project',
        'customer' => 'Customer',
        'completed_at' => 'Completed',
    ],

    'legal_document' => [
        'name' => 'Document version',
        'document' => 'Document',
        'version' => 'Version number',
        'published_at' => 'Published at',
        'json_i18n' => 'Title and content per language',
    ],

    'cookie_consent' => [
        'recorded_at' => 'Recorded at',
        'consent_id' => 'Consent identifier',
        'policy_version' => 'Policy version',
        'decision' => 'Decision',
        'categories' => 'Categories',
    ],

    'holiday' => [
        'name' => 'Name',
        'country_id' => 'Country',
        'holiday_date' => 'Date',
        'source' => 'Source',
        'is_day_off' => 'Day off work',
    ],

];
