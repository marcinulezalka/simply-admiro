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
 * Polecenie będące innym poleceniem z ustalonymi argumentami (np. `deploy:lock` = `private:install --deploy`).
 */
final readonly class PresetCommand implements Command
{
    /**
     * @param  list<string>  $preset  Argumenty dopisywane przed argumentami użytkownika.
     */
    public function __construct(
        private string $name,
        private string $description,
        private Command $command,
        private array $preset,
    ) {}

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function run(array $arguments, string $workingDirectory, $stdout, $stderr): int
    {
        return $this->command->run([...$this->preset, ...$arguments], $workingDirectory, $stdout, $stderr);
    }
}
