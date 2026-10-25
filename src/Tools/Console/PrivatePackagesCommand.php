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

namespace Simplysmart\Admiro\Tools\Console;

use Simplysmart\Admiro\Tools\PrivatePackages\ComposerRunner;
use Simplysmart\Admiro\Tools\PrivatePackages\DeployManifest;
use Simplysmart\Admiro\Tools\PrivatePackages\GitRepositorySource;
use Simplysmart\Admiro\Tools\PrivatePackages\PrivatePackages;
use Simplysmart\Admiro\Tools\PrivatePackages\PrivatePackagesException;
use Simplysmart\Admiro\Tools\PrivatePackages\RepositorySource;

/**
 * Polecenie `simplysmart private:install` (i vendor/bin/composer-private): instaluje paczki prywatne z private-packages.json
 * (albo pokazuje plan przy --dry-run). Bez listy kończy się sukcesem — publiczny klon nic nie instaluje.
 */
final readonly class PrivatePackagesCommand implements Command
{
    public const string NAME = 'private:install';

    public const string DEPLOY = '--deploy';

    public const string USAGE = <<<'USAGE'
        Użycie: composer-private [--deploy] [--dry-run] [--root=<katalog>]

          --deploy          manifest wdrożeniowy: composer.deploy.json i composer.deploy.lock z paczkami
                            z repozytoriów git (zamiast katalogów path), bez instalacji
          --dry-run         pokazuje paczki; nie zapisuje plików i nie uruchamia composera
          --root=<katalog>  katalog projektu (domyślnie bieżący)
        USAGE;

    private const string DRY_RUN_NOTICE = 'Tryb --dry-run: pliki nie zostały zmienione.';

    /**
     * Lock wdrożeniowy bez instalacji paczek i bez skryptów projektu (vendor/ zostaje bez zmian).
     */
    private const array DEPLOY_OPTIONS = ['--no-install', '--no-scripts'];

    public function __construct(
        private ComposerRunner $composer,
        private RepositorySource $repositories = new GitRepositorySource(),
    ) {}

    public function name(): string
    {
        return self::NAME;
    }

    public function description(): string
    {
        return 'Instalacja paczek prywatnych z private-packages.json (= vendor/bin/composer-private)';
    }

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
        $deploy = false;

        foreach ($arguments as $argument) {
            if ($argument === '--dry-run') {
                $dryRun = true;
            } elseif ($argument === self::DEPLOY) {
                $deploy = true;
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

            if ($deploy) {
                return $this->deploy($private, $packages, $dryRun, $stdout, $stderr);
            }

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

    /**
     * Manifest i lock wdrożeniowy: paczki z repozytoriów path wymagane przez projekt dostają repozytoria git
     * (`origin`), a composer zapisuje lock bez instalacji. Paczki z niewypchniętymi commitami są zgłaszane.
     *
     * @param  array<string, string>  $packages  Paczki prywatne.
     * @param  resource  $stdout
     * @param  resource  $stderr
     *
     * @throws PrivatePackagesException
     */
    private function deploy(PrivatePackages $private, array $packages, bool $dryRun, $stdout, $stderr): int
    {
        $deploy = new DeployManifest($private->root(), $this->repositories);
        $sources = $deploy->packages($packages);

        if ($sources === []) {
            fwrite($stdout, "Brak paczek z repozytoriów path — manifest wdrożeniowy niepotrzebny.\n");

            return 0;
        }

        $manifest = $deploy->manifest($packages, $sources);

        foreach ($deploy->unpushed($sources) as $name) {
            fwrite($stderr, "Uwaga: {$name} ma niewypchnięte commity — lock wskaże kod z repozytorium zdalnego.\n");
        }

        $summary = 'Paczki z repozytoriów git: ' . implode(', ', array_keys($sources)) . '.';

        if ($dryRun) {
            fwrite($stdout, "{$summary} " . self::DRY_RUN_NOTICE . "\n");

            return 0;
        }

        $private->prepareDeploy($manifest);
        fwrite($stdout, "{$summary}\n");

        return $this->composer->update($private->root(), PrivatePackages::DEPLOY_MANIFEST, array_keys($sources), self::DEPLOY_OPTIONS);
    }
}
