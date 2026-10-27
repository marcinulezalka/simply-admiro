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

namespace Simplysmart\Admiro\Organizations;

use Simplysmart\Admiro\Database\ColumnNames;
use Simplysmart\Admiro\Models\Organization;

/**
 * Drzewo z listy organizacji (np. aktywnych): węzeł to organizacja z dziećmi z tej samej listy; organizacja, której
 * nadrzędnej nie ma na liście, jest korzeniem. Kolejność rodzeństwa jak na liście wejściowej (np. po nazwie).
 */
final readonly class OrganizationTree
{
    /**
     * @param iterable<Organization> $organizations
     *
     * @return list<array{organization: Organization, children: list<mixed>}> Węzły (children: list tego samego kształtu).
     */
    public static function of(iterable $organizations): array
    {
        $list = [];

        foreach ($organizations as $organization) {
            $list[] = $organization;
        }

        $ids = array_map(static fn(Organization $organization): int => $organization->id, $list);
        $children = [];
        $roots = [];

        foreach ($list as $organization) {
            $parentId = $organization->getAttribute(ColumnNames::PARENT_ID);

            if (is_int($parentId) && $parentId !== $organization->id && in_array($parentId, $ids, true)) {
                $children[$parentId][] = $organization;
            } else {
                $roots[] = $organization;
            }
        }

        return array_map(static fn(Organization $root): array => self::node($root, $children, []), $roots);
    }

    /**
     * @param array<int, list<Organization>> $children
     * @param list<int> $path Przodkowie (ochrona przed cyklem w danych).
     *
     * @return array{organization: Organization, children: list<mixed>}
     */
    private static function node(Organization $organization, array $children, array $path): array
    {
        $path[] = $organization->id;
        $nodes = [];

        foreach ($children[$organization->id] ?? [] as $child) {
            if (! in_array($child->id, $path, true)) {
                $nodes[] = self::node($child, $children, $path);
            }
        }

        return ['organization' => $organization, 'children' => $nodes];
    }
}
