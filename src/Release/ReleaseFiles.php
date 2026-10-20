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

namespace Simplysmart\Tools\Release;

/**
 * Pliki wydania w katalogu celu (projektu albo pakietu): odczyt CHANGELOG.md i VERSION
 * oraz ich wspólny, atomowy zapis.
 */
final readonly class ReleaseFiles
{
    public const string CHANGELOG = 'CHANGELOG.md';

    public const string VERSION = 'VERSION';

    /**
     * @param  string  $root  Katalog celu wydania.
     */
    public function __construct(
        private string $root,
    ) {}

    /**
     * @throws ReleaseException Gdy pliku CHANGELOG.md nie ma albo nie da się go odczytać.
     */
    public function changelog(): Changelog
    {
        return new Changelog($this->read(self::CHANGELOG));
    }

    /**
     * @throws ReleaseException Gdy pliku VERSION nie ma, nie da się go odczytać albo wersja jest niepoprawna.
     */
    public function version(): SemanticVersion
    {
        return SemanticVersion::parse(trim($this->read(self::VERSION)));
    }

    /**
     * Zapisuje wynik wydania tak, aby żaden plik nie został zapisany częściowo.
     *
     * Najpierw zapisuje wszystkie treści do plików tymczasowych obok docelowych,
     * a dopiero gdy wszystkie się udadzą, podmienia je atomowym rename().
     *
     * @throws ReleaseException Gdy zapis lub podmiana któregokolwiek pliku się nie powiodły.
     */
    public function save(ReleasePlan $plan): void
    {
        $contents = [
            $this->path(self::CHANGELOG) => $plan->changelog,
            $this->path(self::VERSION) => $plan->to . "\n",
        ];
        $temporary = [];

        foreach ($contents as $path => $content) {
            $temporaryPath = $path . '.release-' . getmypid();

            if (file_put_contents($temporaryPath, $content) === false) {
                array_map('unlink', $temporary);

                throw new ReleaseException("Nie można zapisać pliku {$path}.");
            }

            if (is_file($path)) {
                chmod($temporaryPath, fileperms($path) & 0777);
            }

            $temporary[$path] = $temporaryPath;
        }

        foreach ($temporary as $path => $temporaryPath) {
            if (! rename($temporaryPath, $path)) {
                throw new ReleaseException("Nie można podmienić pliku {$path}.");
            }
        }
    }

    /**
     * @throws ReleaseException Gdy pliku nie ma albo nie da się go odczytać.
     */
    private function read(string $file): string
    {
        $path = $this->path($file);
        $contents = is_file($path) ? file_get_contents($path) : false;

        return $contents !== false ? $contents : throw new ReleaseException("Nie można odczytać pliku {$path}.");
    }

    private function path(string $file): string
    {
        return "{$this->root}/{$file}";
    }
}
