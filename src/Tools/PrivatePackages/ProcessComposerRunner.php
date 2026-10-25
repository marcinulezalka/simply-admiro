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

namespace Simplysmart\Admiro\Tools\PrivatePackages;

/**
 * Composer w osobnym procesie, ze strumieniami bieżącego procesu. Polecenie podane w konstruktorze
 * (np. `php composer.phar`) ma pierwszeństwo; uruchomiony jako skrypt composera używa tego samego pliku
 * composera (COMPOSER_BINARY), inaczej `composer` z PATH.
 */
final readonly class ProcessComposerRunner implements ComposerRunner
{
    /**
     * @param  list<string>|null  $command  Polecenie Composera; null — COMPOSER_BINARY albo `composer`.
     */
    public function __construct(
        private ?array $command = null,
    ) {}

    public function update(string $root, string $manifest, array $packages, array $options = []): int
    {
        $binary = getenv('COMPOSER_BINARY');
        $composer = $this->command ?? (is_string($binary) && $binary !== '' ? [PHP_BINARY, $binary] : ['composer']);
        $environment = [...getenv(), 'COMPOSER' => $manifest];

        $process = proc_open([...$composer, 'update', ...$packages, ...$options, '--no-interaction'], [STDIN, STDOUT, STDERR], $pipes, $root, $environment);

        if (! is_resource($process)) {
            throw new PrivatePackagesException('Nie udało się uruchomić composera.');
        }

        return proc_close($process);
    }
}
