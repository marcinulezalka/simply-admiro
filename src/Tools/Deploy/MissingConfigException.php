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

namespace Simplysmart\Admiro\Tools\Deploy;

/**
 * W konfiguracji wdrożenia brakuje wymaganych kluczy albo samego pliku.
 */
final class MissingConfigException extends DeployConfigException
{
    /**
     * @param  list<string>  $keys  Brakujące klucze.
     */
    public static function keys(string $file, array $keys): self
    {
        return new self("Brak wymaganych wartości w {$file}: " . implode(', ', $keys) . '. Wzór: ' . $file . EnvFile::EXAMPLE_SUFFIX . '.');
    }

    public static function file(string $file): self
    {
        return new self("Brak {$file} — skopiuj {$file}" . EnvFile::EXAMPLE_SUFFIX . ' i uzupełnij.');
    }
}
