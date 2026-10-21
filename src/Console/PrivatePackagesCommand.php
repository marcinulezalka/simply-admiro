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

namespace Simplysmart\Tools\Console;

use Simplysmart\Tools\PrivatePackages\ComposerRunner;
use Simplysmart\Tools\PrivatePackages\PrivatePackages;
use Simplysmart\Tools\PrivatePackages\PrivatePackagesException;

/**
 * Polecenie vendor/bin/composer-private: instaluje paczki prywatne z private-packages.json
 * (albo pokazuje plan przy --dry-run). Bez listy kończy się sukcesem — publiczny klon nic nie instaluje.
 */
final readonly class PrivatePackagesCommand
{
    public const string USAGE = <<<'USAGE'
        Użycie: composer-private [--dry-run] [--root=<katalog>]

          --dry-run         pokazuje paczki do instalacji; nie zapisuje plików i nie uruchamia composera
          --root=<katalog>  katalog projektu (domyślnie bieżący)
        USAGE;

    private const string DRY_RUN_NOTICE = 'Tryb --dry-run: pliki nie zostały zmienione.';

    public function __construct(
        private ComposerRunner $composer,
    ) {}

    /**
     * @param  list<string>  $arguments  Argumenty bez nazwy skryptu.
     * @param  string  $workingDirectory  Domyślny katalog projektu.
     * @param  resource  $stdout  Strumień komunikatów.
     * @param  resource  $stderr  Strumień błędów.
     * @return int Kod wyjścia: 0 — sukces lub brak paczek, 1 — błąd, inny — kod composera.
     */
    public function run(array $arguments, string $workingDirectory, $stdout, $stderr): int
    {
        $root = $workingDirectory;
        $dryRun = false;

        foreach ($arguments as $argument) {
            if ($argument === '--dry-run') {
                $dryRun = true;
            } elseif (str_starts_with($argument, '--root=')) {
                $root = substr($argument, strlen('--root='));
            } else {
                fwrite($stderr, "Nieznana opcja: {$argument}\n" . self::USAGE . "\n");

                return 1;
            }
        }

        try {
            $private = new PrivatePackages($root);
            $packages = $private->packages();

            if ($packages === []) {
                fwrite($stdout, 'Brak paczek prywatnych (' . PrivatePackages::LIST . ") — nic do zainstalowania.\n");

                return 0;
            }

            $summary = 'Paczki prywatne: ' . implode(', ', array_keys($packages)) . '.';

            if ($dryRun) {
                $private->manifest($packages);
                fwrite($stdout, "{$summary} " . self::DRY_RUN_NOTICE . "\n");

                return 0;
            }

            $private->prepare($packages);
            fwrite($stdout, "{$summary}\n");

            return $this->composer->update($private->root(), PrivatePackages::MANIFEST, array_keys($packages));
        } catch (PrivatePackagesException $exception) {
            fwrite($stderr, $exception->getMessage() . "\n");

            return 1;
        }
    }
}
