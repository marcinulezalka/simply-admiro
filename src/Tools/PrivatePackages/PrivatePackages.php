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
 * Paczki prywatne projektu: lista w `private-packages.json` (poza repozytorium), scalana z
 * `composer.json` w `composer.private.json`, którym composer instaluje je obok publicznych.
 * Publiczny `composer.json` i `composer.lock` zostają bez zmian.
 */
final readonly class PrivatePackages
{
    public const string LIST = 'private-packages.json';

    public const string PUBLIC_MANIFEST = 'composer.json';

    public const string PUBLIC_LOCK = 'composer.lock';

    public const string MANIFEST = 'composer.private.json';

    public const string LOCK = 'composer.private.lock';

    public const string DEPLOY_MANIFEST = 'composer.deploy.json';

    public const string DEPLOY_LOCK = 'composer.deploy.lock';

    private const string PACKAGE_NAME = '/^[a-z0-9]([_.-]?[a-z0-9]+)*\/[a-z0-9](([_.]|-{1,2})?[a-z0-9]+)*$/';

    /**
     * @param  string  $root  Katalog projektu.
     */
    public function __construct(
        private string $root,
    ) {}

    /**
     * Paczki prywatne z listy (nazwa => wersja); pusta tablica, gdy listy nie ma.
     *
     * @return array<string, string>
     *
     * @throws PrivatePackagesException Gdy lista jest nieczytelna albo ma błędny format.
     */
    public function packages(): array
    {
        if (! is_file($this->path(self::LIST))) {
            return [];
        }

        $require = $this->decode(self::LIST)->require ?? null;

        if (! $require instanceof stdClass) {
            throw new PrivatePackagesException('Plik ' . self::LIST . ' musi mieć obiekt "require" (nazwa paczki => wersja).');
        }

        $packages = [];

        foreach (get_object_vars($require) as $name => $constraint) {
            if (! is_string($name) || preg_match(self::PACKAGE_NAME, $name) !== 1 || ! is_string($constraint) || $constraint === '') {
                throw new PrivatePackagesException("Błędny wpis w " . self::LIST . ": \"{$name}\".");
            }

            $packages[$name] = $constraint;
        }

        return $packages;
    }

    /**
     * Treść composer.private.json: composer.json z dopisanymi paczkami prywatnymi w "require".
     *
     * @param  array<string, string>  $packages
     *
     * @throws PrivatePackagesException Gdy composer.json jest nieczytelny.
     */
    public function manifest(array $packages): string
    {
        $manifest = $this->decode(self::PUBLIC_MANIFEST);
        $require = $manifest->require ?? new stdClass();

        if (! $require instanceof stdClass) {
            throw new PrivatePackagesException('Pole "require" w ' . self::PUBLIC_MANIFEST . ' musi być obiektem.');
        }

        foreach ($packages as $name => $constraint) {
            $require->{$name} = $constraint;
        }

        $manifest->require = $require;

        return json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . "\n";
    }

    /**
     * Zapisuje composer.private.json, a przy pierwszym uruchomieniu kopiuje composer.lock jako
     * composer.private.lock (wersje paczek publicznych zostają te same).
     *
     * @param  array<string, string>  $packages
     *
     * @throws PrivatePackagesException Gdy zapisu nie da się wykonać.
     */
    public function prepare(array $packages): void
    {
        $this->write(self::MANIFEST, $this->manifest($packages));

        if (! is_file($this->path(self::LOCK)) && is_file($this->path(self::PUBLIC_LOCK))
            && ! copy($this->path(self::PUBLIC_LOCK), $this->path(self::LOCK))) {
            throw new PrivatePackagesException('Nie udało się utworzyć ' . self::LOCK . '.');
        }
    }

    /**
     * Zapisuje composer.deploy.json i świeżą kopię locka (composer.private.lock, a bez niego composer.lock) jako
     * composer.deploy.lock — wersje paczek spoza repozytoriów path zostają te same.
     *
     * @throws PrivatePackagesException Gdy zapisu nie da się wykonać.
     */
    public function prepareDeploy(string $manifest): void
    {
        $this->write(self::DEPLOY_MANIFEST, $manifest);
        $source = is_file($this->path(self::LOCK)) ? self::LOCK : self::PUBLIC_LOCK;

        if (is_file($this->path($source)) && ! copy($this->path($source), $this->path(self::DEPLOY_LOCK))) {
            throw new PrivatePackagesException('Nie udało się utworzyć ' . self::DEPLOY_LOCK . '.');
        }
    }

    public function root(): string
    {
        return $this->root;
    }

    private function decode(string $file): stdClass
    {
        $contents = is_file($this->path($file)) ? file_get_contents($this->path($file)) : false;

        if ($contents === false) {
            throw new PrivatePackagesException("Brak pliku {$file} w {$this->root}.");
        }

        try {
            $decoded = json_decode($contents, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new PrivatePackagesException("Plik {$file} nie jest poprawnym JSON-em: {$exception->getMessage()}.");
        }

        if (! $decoded instanceof stdClass) {
            throw new PrivatePackagesException("Plik {$file} musi zawierać obiekt JSON.");
        }

        return $decoded;
    }

    /**
     * Zapis atomowy: plik tymczasowy obok docelowego, potem rename().
     */
    private function write(string $file, string $contents): void
    {
        $temporary = $this->path($file) . '.tmp';

        if (file_put_contents($temporary, $contents) === false || ! rename($temporary, $this->path($file))) {
            throw new PrivatePackagesException("Nie udało się zapisać {$file}.");
        }
    }

    private function path(string $file): string
    {
        return rtrim($this->root, '/') . '/' . $file;
    }
}
