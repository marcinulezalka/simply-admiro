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

use Simplysmart\Admiro\Tools\Release\Changelog;
use Simplysmart\Admiro\Tools\Release\ReleaseException;
use Simplysmart\Admiro\Tools\Release\ReleaseFiles;
use Simplysmart\Admiro\Tools\Release\VersionRelease;

/**
 * Polecenie `simplysmart release` (i vendor/bin/version-release): zamienia argumenty na wydanie (albo jego podgląd
 * przy --dry-run) i wynik na komunikat.
 */
final readonly class VersionReleaseCommand implements Command
{
    public const string NAME = 'release';

    private const string DRY_RUN_NOTICE = 'Tryb --dry-run: pliki nie zostały zmienione.';

    public function __construct(
        private VersionRelease $release,
    ) {}

    public function name(): string
    {
        return self::NAME;
    }

    public function description(): string
    {
        return 'Wydanie wersji z sekcji [Unreleased] w CHANGELOG.md (= vendor/bin/version-release)';
    }

    /**
     * @param  list<string>  $arguments  Argumenty bez nazwy skryptu.
     * @param  string  $workingDirectory  Domyślny katalog celu wydania.
     * @param  resource  $stdout  Strumień komunikatu o powodzeniu.
     * @param  resource  $stderr  Strumień komunikatu o błędzie.
     * @return int Kod wyjścia procesu: 0 — sukces lub brak wpisów, 1 — błąd.
     */
    public function run(array $arguments, string $workingDirectory, $stdout, $stderr): int
    {
        try {
            $options = ReleaseOptions::fromArguments($arguments, $workingDirectory);
            $files = new ReleaseFiles($options->root);
            $plan = $options->dryRun
                ? $this->release->plan($files, $options->majorConfirmed)
                : $this->release->release($files, $options->majorConfirmed);
        } catch (ReleaseException $exception) {
            fwrite($stderr, $exception->getMessage() . "\n");

            return 1;
        }

        if ($plan === null) {
            fwrite($stdout, 'Sekcja ' . Changelog::UNRELEASED_TITLE . " nie ma wpisów — nic do wydania.\n");

            return 0;
        }

        $summary = "Wersja {$plan->from} → {$plan->to} ({$plan->bump->value}).";
        fwrite($stdout, ($options->dryRun ? "{$summary} " . self::DRY_RUN_NOTICE : $summary) . "\n");

        return 0;
    }
}
