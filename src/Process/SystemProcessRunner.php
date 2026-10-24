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

namespace Simplysmart\Tools\Process;

/**
 * ProcessRunner na proc_open: `run` przekazuje wyjście procesu na STDOUT/STDERR, `capture` je zbiera.
 */
final readonly class SystemProcessRunner implements ProcessRunner
{
    public function run(array $command, ?string $input = null, ?string $directory = null): int
    {
        $stdin = $input === null ? STDIN : ['pipe', 'r'];
        $process = proc_open($command, [$stdin, STDOUT, STDERR], $pipes, $directory);

        if (! is_resource($process)) {
            throw new ProcessException("Nie udało się uruchomić: {$command[0]}.");
        }

        if ($input !== null) {
            fwrite($pipes[0], $input);
            fclose($pipes[0]);
        }

        return proc_close($process);
    }

    public function capture(array $command, ?string $directory = null): ?string
    {
        $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $directory);

        if (! is_resource($process)) {
            return null;
        }

        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        return proc_close($process) === 0 && is_string($output) ? trim($output) : null;
    }
}
