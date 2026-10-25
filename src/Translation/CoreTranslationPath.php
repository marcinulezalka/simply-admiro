<?php

declare(strict_types=1);

/**
 * This file is part of simplysmart/mainboard.
 *
 * Copyright (c) 2026 Marcin Ulezalka <marcin.ulezalka@simplysmart.pl>
 *
 * Licensed under the Simplysmart Restricted Source License 1.0.
 * See the LICENSE file distributed with this source code.
 */

namespace Simplysmart\Mainboard\Translation;

use Illuminate\Contracts\Translation\Loader;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;

/**
 * Dokłada tłumaczenia Core (klucze frameworka bez przestrzeni nazw, np. validation.*) do loadera.
 *
 * Kolejność ścieżek decyduje o pierwszeństwie (późniejsza nadpisuje wcześniejszą), więc katalog
 * Core trafia między tłumaczenia frameworka a lang/ aplikacji: Core zastępuje angielskie
 * domyślne teksty, a plik w lang/<locale>/ szkieletu nadal wygrywa nad Core.
 */
final readonly class CoreTranslationPath
{
    /**
     * @param  string  $corePath  katalog lang/ paczki
     * @param  string  $appPath  katalog lang/ aplikacji
     */
    public function __construct(
        private Filesystem $files,
        private string $corePath,
        private string $appPath,
    ) {}

    /**
     * Zwraca loader z katalogiem Core przed katalogiem aplikacji; inny niż plikowy zostawia bez zmian.
     */
    public function extend(Loader $loader): Loader
    {
        if (! $loader instanceof FileLoader) {
            return $loader;
        }

        $paths = array_values(array_filter(
            $loader->paths(),
            fn(mixed $path): bool => is_string($path) && ! in_array($path, [$this->appPath, $this->corePath], true),
        ));

        $extended = new FileLoader($this->files, [...$paths, $this->corePath, $this->appPath]);

        foreach ($loader->jsonPaths() as $jsonPath) {
            if (is_string($jsonPath)) {
                $extended->addJsonPath($jsonPath);
            }
        }

        foreach ($loader->namespaces() as $namespace => $hint) {
            $extended->addNamespace($namespace, $hint);
        }

        return $extended;
    }
}
