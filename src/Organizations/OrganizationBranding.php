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

namespace Simplysmart\Admiro\Organizations;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Simplysmart\Admiro\Database\ColumnNames;
use Simplysmart\Admiro\Enums\BrandAsset;
use Simplysmart\Admiro\Mail\BrandFile;
use Simplysmart\Admiro\Models\Organization;

/**
 * Marka organizacji (logo, sygnet, ikona strony) — jedyne miejsce jej zmiany. Pliki leżą na prywatnym dysku
 * `admiro.organization_brand.disk` (`organizations/<token>/brand/<plik>-<skrót>.<rozszerzenie>`), a wpis
 * `json_attributes.brand.<plik>` (ścieżka, MIME) zmienia się jak inne pola organizacji, więc trafia do historii.
 */
final readonly class OrganizationBranding
{
    public const string ATTRIBUTE = 'brand';

    private const string PATH = 'path';

    private const string MIME = 'mime';

    private const string DIRECTORY = 'organizations/%s/brand';

    public function __construct(
        private FilesystemFactory $filesystems,
        private Repository $config,
    ) {}

    /**
     * Zapisuje plik (rozszerzenie z zawartości, nie z nazwy) i usuwa poprzedni.
     *
     * @throws OrganizationChangeRejected Gdy format nie pasuje do rodzaju pliku albo pliku nie da się odczytać.
     */
    public function store(Organization $organization, BrandAsset $asset, UploadedFile $file): void
    {
        $extension = strtolower((string) $file->guessExtension());
        $contents = $file->get();

        if (! $asset->accepts($extension) || ! is_string($contents)) {
            throw OrganizationChangeRejected::because("the {$asset->value} must be a ." . implode('/.', $asset->extensions()) . ' file.');
        }

        $token = $organization->getAttribute(ColumnNames::TOKEN);

        if (! is_string($token)) {
            throw OrganizationChangeRejected::because('the organization has no token.');
        }

        $path = sprintf(self::DIRECTORY, $token) . "/{$asset->value}-" . substr(hash('xxh3', $contents), 0, 16) . ".{$extension}";
        $previous = $this->entry($organization, $asset)[self::PATH] ?? null;

        $this->disk()->put($path, $contents);
        $this->write($organization, $asset, [self::PATH => $path, self::MIME => BrandAsset::mimeType($extension)]);

        if ($previous !== null && $previous !== $path) {
            $this->disk()->delete($previous);
        }
    }

    public function remove(Organization $organization, BrandAsset $asset): void
    {
        $previous = $this->entry($organization, $asset)[self::PATH] ?? null;

        if ($previous === null) {
            return;
        }

        $this->write($organization, $asset, null);
        $this->disk()->delete($previous);
    }

    public function has(Organization $organization, BrandAsset $asset): bool
    {
        return $this->entry($organization, $asset) !== [];
    }

    /**
     * Plik marki organizacji albo null, gdy go nie ustawiono (albo zniknął z dysku).
     */
    public function file(Organization $organization, BrandAsset $asset): ?BrandFile
    {
        $entry = $this->entry($organization, $asset);
        $path = $entry[self::PATH] ?? null;
        $contents = $path === null ? null : $this->disk()->get($path);

        return $path === null || ! is_string($contents) ? null : new BrandFile($contents, $entry[self::MIME] ?? BrandAsset::mimeType(pathinfo($path, PATHINFO_EXTENSION)), basename($path));
    }

    /**
     * @return array{path?: string, mime?: string}
     */
    private function entry(Organization $organization, BrandAsset $asset): array
    {
        $attributes = $organization->getAttribute(ColumnNames::JSON_ATTRIBUTES);
        $entry = is_array($attributes) && is_array($attributes[self::ATTRIBUTE] ?? null) ? ($attributes[self::ATTRIBUTE][$asset->value] ?? null) : null;

        if (! is_array($entry) || ! is_string($entry[self::PATH] ?? null)) {
            return [];
        }

        return array_filter([self::PATH => $entry[self::PATH], self::MIME => is_string($entry[self::MIME] ?? null) ? $entry[self::MIME] : null], is_string(...));
    }

    /**
     * @param array{path: string, mime: string}|null $entry Null usuwa wpis.
     */
    private function write(Organization $organization, BrandAsset $asset, ?array $entry): void
    {
        $attributes = $organization->getAttribute(ColumnNames::JSON_ATTRIBUTES);
        $attributes = is_array($attributes) ? $attributes : [];
        $brand = is_array($attributes[self::ATTRIBUTE] ?? null) ? $attributes[self::ATTRIBUTE] : [];

        if ($entry === null) {
            unset($brand[$asset->value]);
        } else {
            $brand[$asset->value] = $entry;
        }

        if ($brand === []) {
            unset($attributes[self::ATTRIBUTE]);
        } else {
            $attributes[self::ATTRIBUTE] = $brand;
        }

        $organization->forceFill([ColumnNames::JSON_ATTRIBUTES => $attributes === [] ? null : $attributes])->save();
    }

    private function disk(): Filesystem
    {
        return $this->filesystems->disk($this->config->string('admiro.organization_brand.disk'));
    }
}
