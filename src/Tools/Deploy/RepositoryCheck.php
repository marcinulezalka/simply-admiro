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

namespace Simplysmart\Admiro\Tools\Deploy;

use Simplysmart\Admiro\Tools\PrivatePackages\RepositorySource;
use Simplysmart\Admiro\Tools\Process\ProcessRunner;

/**
 * Sprawdza, czy projekt i paczki z `packages/*\/*` są wypchnięte, zanim serwer pobierze kod z `origin`, i na życzenie
 * (`deploy --with-push`) je wypycha. Katalogi bez `.git` są pomijane (np. projekt bez lokalnych paczek).
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
     * Wypycha (`git push` do śledzonej gałęzi w `origin` każdego repozytorium) repozytoria z niewypchniętymi commitami:
     * najpierw paczki — także prywatne, każda do swojego `origin` — potem projekt, żeby projekt nie wskazywał kodu,
     * którego jeszcze nie ma w repozytorium zdalnym. Repozytoria bez nowych commitów są pomijane.
     *
     * @return list<string> Wypchnięte repozytoria (nazwy względem katalogu projektu).
     *
     * @throws DeployException Gdy któregoś repozytorium nie da się wypchnąć (kolejne nie są już wypychane).
     */
    public function push(string $root): array
    {
        $pushed = [];
        $names = $this->repositories($root);
        $ordered = [...array_values(array_diff($names, [self::PROJECT])), ...array_intersect([self::PROJECT], $names)];

        foreach ($ordered as $name) {
            $directory = $name === self::PROJECT ? $root : "{$root}/{$name}";

            if (! $this->repositories->hasUnpushedCommits($directory)) {
                continue;
            }

            if ($this->processes->run(['git', '-C', $directory, 'push', '--quiet']) !== 0) {
                throw new DeployException("Nie udało się wypchnąć {$name} (git push). Sprawdź dostęp do origin i uruchom ponownie.");
            }

            $pushed[] = $name;
        }

        return $pushed;
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
