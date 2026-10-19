<?php

declare(strict_types=1);

namespace Simplysmart\Tools\Console;

use Simplysmart\Tools\Release\Changelog;
use Simplysmart\Tools\Release\ReleaseException;
use Simplysmart\Tools\Release\ReleaseFiles;
use Simplysmart\Tools\Release\VersionRelease;

/**
 * Polecenie vendor/bin/version-release: zamienia argumenty na wydanie (albo jego podgląd
 * przy --dry-run) i wynik na komunikat.
 */
final readonly class VersionReleaseCommand
{
    private const string DRY_RUN_NOTICE = 'Tryb --dry-run: pliki nie zostały zmienione.';

    public function __construct(
        private VersionRelease $release,
    ) {}

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
