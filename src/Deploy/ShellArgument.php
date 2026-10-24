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

namespace Simplysmart\Tools\Deploy;

/**
 * Cytowanie wartości dla basha na serwerze: każda wartość w apostrofach, apostrof jako `'\''` — nic nie jest
 * rozwijane (`$`, `` ` ``, `\`, `;`), niezależnie od locale (w odróżnieniu od escapeshellarg).
 */
final readonly class ShellArgument
{
    private const string HOME = '~';

    private const string HOME_PREFIX = '~/';

    public static function quote(string $value): string
    {
        return "'" . str_replace("'", "'\\''", $value) . "'";
    }

    /**
     * Ścieżka z zachowaniem `~/` na początku (katalog domowy na serwerze); reszta cytowana.
     */
    public static function path(string $path): string
    {
        if ($path === self::HOME) {
            return self::HOME;
        }

        return str_starts_with($path, self::HOME_PREFIX)
            ? self::HOME_PREFIX . self::quote(substr($path, strlen(self::HOME_PREFIX)))
            : self::quote($path);
    }

    /**
     * @param  list<string>  $arguments
     */
    public static function join(array $arguments): string
    {
        return implode(' ', array_map(self::quote(...), $arguments));
    }
}
