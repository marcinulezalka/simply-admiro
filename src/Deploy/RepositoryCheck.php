<?php

declare(strict_types=1);

/**
 * This file is part of simplysmart/tools.
 *
 * Copyright (c) 2026 Marcin Ulezalka <marcin.ulezalka@simplysmart.pl>
 *
 * Licensed under the Simplysmart Restricted Source License 1.0.
 * See the LICENSE file distributed with this source code.
 */

namespace Simplysmart\Tools\Deploy;

use Simplysmart\Tools\PrivatePackages\RepositorySource;
use Simplysmart\Tools\Process\ProcessRunner;

/**
 * Sprawdza, czy projekt i paczki z `packages/*\/*` są wypchnięte, zanim serwer pobierze kod z `origin`.
 * Katalogi bez `.git` są pomijane (np. projekt bez lokalnych paczek).
 */
final readonly class RepositoryCheck
{
    public const string PROJECT = '.';

    public const string PACKAGES = 'packages/*/*';

    public function __construct(
        private ProcessRunner $processes,
        private RepositorySource $repositories,
    ) {}

    public function check(string $root): RepositoryReport
    {
        $unpushed = $uncommitted = $unfetched = [];

        foreach ($this->repositories($root) as $name) {
            $directory = $name === self::PROJECT ? $root : "{$root}/{$name}";

            if ($this->processes->capture(['git', '-C', $directory, 'fetch', '--quiet', 'origin']) === null) {
                $unfetched[] = $name;
            }

            if ($this->repositories->hasUnpushedCommits($directory)) {
                $unpushed[] = $name;
            }

            if ($this->processes->capture(['git', '-C', $directory, 'status', '--porcelain']) !== '') {
                $uncommitted[] = $name;
            }
        }

        return new RepositoryReport($unpushed, $uncommitted, $unfetched);
    }

    /**
     * @return list<string>
     */
    private function repositories(string $root): array
    {
        $packages = array_map(
            fn(string $directory): string => substr($directory, strlen($root) + 1),
            glob($root . '/' . self::PACKAGES, GLOB_ONLYDIR) ?: [],
        );

        return array_values(array_filter(
            [self::PROJECT, ...$packages],
            fn(string $name): bool => is_dir(($name === self::PROJECT ? $root : "{$root}/{$name}") . '/.git'),
        ));
    }
}
