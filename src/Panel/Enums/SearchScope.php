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

namespace Simplysmart\Admiro\Panel\Enums;

use Simplysmart\Admiro\Panel\Http\AdminContext;

/**
 * Zakresy globalnej wyszukiwarki strefy administratorów (opcje „Szukaj w” przy polu, pole `in[]`): języki, kraje
 * i waluty, organizacje i administratorzy, strony panelu. Zakres jest dostępny z uprawnieniem do jego danych
 * (strony panelu zawsze — menu i tak pokazuje tylko dozwolone). Teksty: `admiro::search.scopes.<value>`.
 */
enum SearchScope: string
{
    case Languages = 'languages';
    case Dictionaries = 'dictionaries';
    case Directory = 'directory';
    case Pages = 'pages';

    /**
     * Zakresy, w których administrator może szukać, w stałej kolejności.
     *
     * @return list<self>
     */
    public static function available(AdminContext $context): array
    {
        return array_values(array_filter(self::cases(), static fn(self $scope): bool => $scope->allows($context)));
    }

    public function allows(AdminContext $context): bool
    {
        return match ($this) {
            self::Languages, self::Dictionaries => $context->can(Permission::SettingsView),
            self::Directory => $context->can(Permission::OrganizationsView) || $context->can(Permission::UsersManage),
            self::Pages => true,
        };
    }

    /**
     * Ikona przy nazwie zakresu w menu „Szukaj w”.
     */
    public function icon(): string
    {
        return match ($this) {
            self::Languages => 'locale',
            self::Dictionaries => 'dictionaries',
            self::Directory => 'building',
            self::Pages => 'sitemap',
        };
    }

    public function label(): string
    {
        return text('admiro::search.scopes.' . $this->value);
    }
}
