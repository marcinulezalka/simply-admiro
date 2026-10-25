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

use JsonException;
use stdClass;

/**
 * Manifest wdrożeniowy (composer.deploy.json): composer.json bez repozytoriów typu path, z repozytorium VCS
 * (adres `origin` z RepositorySource) każdej paczki z katalogów tych repozytoriów, którą projekt wymaga
 * (`require`, `require-dev` albo paczki prywatne), i z dopisanymi paczkami prywatnymi. Lokalnie paczki zostają
 * dowiązaniami z katalogów, a na serwerze composer pobiera je z ich repozytoriów git.
 */
final readonly class DeployManifest
{
    private const string PATH_TYPE = 'path';

    private const string VCS_TYPE = 'vcs';

    public function __construct(
        private string $root,
        private RepositorySource $repositories,
    ) {}

    /**
     * Paczki z katalogów repozytoriów path wymagane przez projekt: nazwa => katalog.
     *
     * @param  array<string, string>  $private  Paczki prywatne (nazwa => wersja).
     * @return array<string, string>
     *
     * @throws PrivatePackagesException Gdy composer.json jest nieczytelny.
     */
    public function packages(array $private): array
    {
        $manifest = $this->decode($this->path(PrivatePackages::PUBLIC_MANIFEST));
        $required = [...array_keys(self::map($manifest->require ?? null)), ...array_keys(self::map($manifest->{'require-dev'} ?? null)), ...array_keys($private)];
        $packages = [];

        foreach (self::pathPatterns($manifest) as $pattern) {
            foreach (glob($this->path($pattern), GLOB_ONLYDIR) ?: [] as $directory) {
                $name = is_file($directory . '/composer.json') ? ($this->decode($directory . '/composer.json')->name ?? null) : null;

                if (is_string($name) && in_array($name, $required, true)) {
                    $packages[$name] = $directory;
                }
            }
        }

        ksort($packages);

        return $packages;
    }

    /**
     * Treść composer.deploy.json.
     *
     * @param  array<string, string>  $private  Paczki prywatne (nazwa => wersja).
     * @param  array<string, string>  $packages  Paczki z katalogów (nazwa => katalog), zob. packages().
     *
     * @throws PrivatePackagesException Gdy paczka nie ma repozytorium z `origin` albo manifest jest nieczytelny.
     */
    public function manifest(array $private, array $packages): string
    {
        $manifest = $this->decode($this->path(PrivatePackages::PUBLIC_MANIFEST));
        $require = $manifest->require ?? new stdClass();

        if (! $require instanceof stdClass) {
            throw new PrivatePackagesException('Pole "require" w ' . PrivatePackages::PUBLIC_MANIFEST . ' musi być obiektem.');
        }

        foreach ($private as $name => $constraint) {
            $require->{$name} = $constraint;
        }

        $repositories = array_values(array_filter(
            is_array($manifest->repositories ?? null) ? $manifest->repositories : [],
            static fn(mixed $repository): bool => ! ($repository instanceof stdClass && ($repository->type ?? null) === self::PATH_TYPE),
        ));

        foreach ($packages as $name => $directory) {
            $url = $this->repositories->originUrl($directory) ?? throw new PrivatePackagesException("Paczka {$name} ({$directory}) nie ma repozytorium git z origin.");
            $repositories[] = (object) ['type' => self::VCS_TYPE, 'url' => $url];
        }

        $manifest->require = $require;
        $manifest->repositories = $repositories;

        return json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . "\n";
    }

    /**
     * Paczki z commitami niewypchniętymi (lock wskazałby starszy kod z repozytorium zdalnego).
     *
     * @param  array<string, string>  $packages  Nazwa => katalog.
     * @return list<string>
     */
    public function unpushed(array $packages): array
    {
        return array_keys(array_filter($packages, $this->repositories->hasUnpushedCommits(...)));
    }

    /**
     * @return list<string> Wzorce katalogów repozytoriów path (np. `packages/*\/*`).
     */
    private static function pathPatterns(stdClass $manifest): array
    {
        $patterns = [];

        foreach (is_array($manifest->repositories ?? null) ? $manifest->repositories : [] as $repository) {
            if ($repository instanceof stdClass && ($repository->type ?? null) === self::PATH_TYPE && is_string($repository->url ?? null)) {
                $patterns[] = $repository->url;
            }
        }

        return $patterns;
    }

    /**
     * @return array<array-key, mixed>
     */
    private static function map(mixed $value): array
    {
        return $value instanceof stdClass ? get_object_vars($value) : [];
    }

    private function decode(string $file): stdClass
    {
        $contents = is_file($file) ? file_get_contents($file) : false;

        if ($contents === false) {
            throw new PrivatePackagesException("Brak pliku {$file}.");
        }

        try {
            $decoded = json_decode($contents, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new PrivatePackagesException("Plik {$file} nie jest poprawnym JSON-em: {$exception->getMessage()}.");
        }

        return $decoded instanceof stdClass ? $decoded : throw new PrivatePackagesException("Plik {$file} musi zawierać obiekt JSON.");
    }

    private function path(string $file): string
    {
        return rtrim($this->root, '/') . '/' . $file;
    }
}
