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

namespace Simplysmart\Mainboard\Database;

/**
 * Nazwy kolumn standardowych (Columns::base(), Columns::closing()); migracje, modele i testy
 * używają tych stałych, nie literałów.
 */
final class ColumnNames
{
    public const string ID = 'id';

    public const string TOKEN = 'token';

    public const string PARENT_ID = 'parent_id';

    public const string NAME = 'name';

    public const string STATUS = 'status';

    public const string IS_ACTIVE = 'is_active';

    public const string IS_DEFAULT = 'is_default';

    public const string SORT_ORDER = 'sort_order';

    public const string JSON_ATTRIBUTES = 'json_attributes';

    public const string JSON_DEFAULT_SETTINGS = 'json_default_settings';

    public const string JSON_TAGS = 'json_tags';

    public const string JSON_I18N = 'json_i18n';

    public const string JSON_MIGRATIONS = 'json_migrations';

    public const string CHECKSUM = 'checksum';

    public const string JSON_ARCHIVE = 'json_archive';

    public const string CREATED_AT = 'created_at';

    public const string UPDATED_AT = 'updated_at';

    public const string DELETED_AT = 'deleted_at';

    /**
     * Kolumny techniczne pomijane w sumie kontrolnej rekordu (docs/baza-danych.md §10).
     *
     * @var list<string>
     */
    public const array CHECKSUM_EXCLUDED = [
        self::ID,
        self::TOKEN,
        self::CHECKSUM,
        self::JSON_MIGRATIONS,
        self::JSON_ARCHIVE,
        self::CREATED_AT,
        self::UPDATED_AT,
        self::DELETED_AT,
    ];
}
