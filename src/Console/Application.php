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

/**
 * Wejście `vendor/bin/simplysmart`: wybiera polecenie po nazwie (jak artisan; kropka działa jak dwukropek,
 * `deploy.onboarding` = `deploy:onboarding`), bez nazwy albo z `list` pokazuje listę poleceń.
 */
final readonly class Application
{
    public const string LIST = 'list';

    private const string SEPARATOR = ':';

    private const string SEPARATOR_ALIAS = '.';

    private const array HELP = ['-h', '--help', 'help'];

    /**
     * @var array<string, Command>
     */
    private array $commands;

    /**
     * @param  list<Command>  $commands
     */
    public function __construct(array $commands)
    {
        $byName = [];

        foreach ($commands as $command) {
            $byName[$command->name()] = $command;
        }

        ksort($byName);
        $this->commands = $byName;
    }

    /**
     * @param  list<string>  $arguments  Argumenty bez nazwy skryptu: nazwa polecenia i jego argumenty.
     * @param  resource  $stdout
     * @param  resource  $stderr
     */
    public function run(array $arguments, string $workingDirectory, $stdout, $stderr): int
    {
        $name = str_replace(self::SEPARATOR_ALIAS, self::SEPARATOR, $arguments[0] ?? self::LIST);

        if ($name === self::LIST || in_array($name, self::HELP, true)) {
            fwrite($stdout, $this->usage());

            return 0;
        }

        $command = $this->commands[$name] ?? null;

        if ($command === null) {
            fwrite($stderr, "Nieznane polecenie: {$name}\n\n" . $this->usage());

            return 1;
        }

        return $command->run(array_slice($arguments, 1), $workingDirectory, $stdout, $stderr);
    }

    private function usage(): string
    {
        $width = max(array_map(strlen(...), [self::LIST, ...array_keys($this->commands)]));
        $lines = ['Użycie: vendor/bin/simplysmart <polecenie> [opcje]', '', 'Polecenia:'];
        $lines[] = '  ' . str_pad(self::LIST, $width) . '  Lista poleceń';

        foreach ($this->commands as $name => $command) {
            $lines[] = '  ' . str_pad($name, $width) . '  ' . $command->description();
        }

        return implode("\n", $lines) . "\n";
    }
}
