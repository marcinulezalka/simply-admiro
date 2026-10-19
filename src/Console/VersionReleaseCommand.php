<?php

declare(strict_types=1);

namespace Simplysmart\Tools\Console;

use Simplysmart\Tools\Release\Changelog;
use Simplysmart\Tools\Release\ReleaseException;
use Simplysmart\Tools\Release\ReleaseFiles;
use Simplysmart\Tools\Release\VersionRelease;

/**
 * Polecenie vendor/bin/version-release: zamienia argumenty na wydanie i wynik na komunikat.
 */
final readonly class VersionReleaseCommand
{
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
            $plan = $this->release->release(new ReleaseFiles($options->root), $options->majorConfirmed);
        } catch (ReleaseException $exception) {
            fwrite($stderr, $exception->getMessage() . "\n");

            return 1;
        }

        fwrite($stdout, $plan === null
            ? 'Sekcja ' . Changelog::UNRELEASED_TITLE . " nie ma wpisów — nic do wydania.\n"
            : "Wersja {$plan->from} → {$plan->to} ({$plan->bump->value}).\n");

        return 0;
    }
}
