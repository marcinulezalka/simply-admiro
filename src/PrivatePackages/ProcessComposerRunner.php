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

namespace Simplysmart\Tools\PrivatePackages;

/**
 * Composer w osobnym procesie, ze strumieniami bieżącego procesu. Uruchomiony jako skrypt
 * composera używa tego samego pliku composera (COMPOSER_BINARY), inaczej `composer` z PATH.
 */
final readonly class ProcessComposerRunner implements ComposerRunner
{
    public function update(string $root, string $manifest, array $packages): int
    {
        $binary = getenv('COMPOSER_BINARY');
        $composer = is_string($binary) && $binary !== '' ? [PHP_BINARY, $binary] : ['composer'];
        $environment = [...getenv(), 'COMPOSER' => $manifest];

        $process = proc_open([...$composer, 'update', ...$packages, '--no-interaction'], [STDIN, STDOUT, STDERR], $pipes, $root, $environment);

        if (! is_resource($process)) {
            throw new PrivatePackagesException('Nie udało się uruchomić composera.');
        }

        return proc_close($process);
    }
}
