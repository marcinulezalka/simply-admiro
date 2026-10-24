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

namespace Simplysmart\Mainboard\Queries;

use Illuminate\Support\Arr;

/**
 * Zewnętrzne pakiety Composera, od których aplikacja zależy bezpośrednio, do strony licencji: wymagane (`require`)
 * przez aplikację (`composer.json`) i przez jej własne paczki (pakiety z vendorem aplikacji, z repozytoriów `path`
 * albo git), bez ich dalszych zależności (np. Laravel bez pakietów, których sam wymaga), bez pakietów platformy
 * (`php`, `ext-*`) i bez własnych paczek; dane z `vendor/composer/installed.json`, posortowane po nazwie.
 */
final readonly class InstalledPackages
{
    public function __construct(
        private string $installedJsonPath,
        private string $composerJsonPath,
    ) {}

    public static function forApplication(): self
    {
        return new self(base_path('vendor/composer/installed.json'), base_path('composer.json'));
    }

    /**
     * @return list<array{name: string, version: string, licenses: list<string>, description: string, homepage: ?string}>
     */
    public function all(): array
    {
        $installed = $this->json($this->installedJsonPath);
        $root = $this->json($this->composerJsonPath);

        if (! is_array($installed['packages'] ?? null)) {
            return [];
        }

        $byName = [];

        foreach ($installed['packages'] as $package) {
            if (is_array($package) && is_string($package['name'] ?? null)) {
                $byName[$package['name']] = $package;
            }
        }

        $ownVendor = is_string($root['name'] ?? null) ? strstr($root['name'], '/', true) . '/' : null;
        $direct = $this->requiredNames($root);

        foreach ($byName as $name => $package) {
            if ($ownVendor !== null && str_starts_with($name, $ownVendor)) {
                $direct = [...$direct, ...$this->requiredNames($package)];
            }
        }

        $packages = [];

        foreach (array_unique($direct) as $name) {
            $package = $byName[$name] ?? null;

            if ($package === null || ($ownVendor !== null && str_starts_with($name, $ownVendor))) {
                continue;
            }

            $sourceUrl = Arr::get($package, 'source.url');
            $packages[] = [
                'name' => $name,
                'version' => is_string($package['version'] ?? null) ? $package['version'] : '',
                'licenses' => array_values(array_filter(is_array($package['license'] ?? null) ? $package['license'] : [], is_string(...))),
                'description' => is_string($package['description'] ?? null) ? $package['description'] : '',
                'homepage' => is_string($package['homepage'] ?? null) ? $package['homepage'] : (is_string($sourceUrl) ? preg_replace('/\.git$/', '', $sourceUrl) : null),
            ];
        }

        usort($packages, static fn(array $first, array $second): int => strcmp($first['name'], $second['name']));

        return $packages;
    }

    /**
     * Nazwy pakietów z `require` (bez pakietów platformy: `php`, `ext-*`, `lib-*`, `composer-*`).
     *
     * @param array<array-key, mixed> $manifest
     *
     * @return list<string>
     */
    private function requiredNames(array $manifest): array
    {
        $require = is_array($manifest['require'] ?? null) ? $manifest['require'] : [];

        return array_values(array_filter(array_map(strval(...), array_keys($require)), static fn(string $name): bool => str_contains($name, '/')));
    }

    /**
     * @return array<array-key, mixed>
     */
    private function json(string $path): array
    {
        $decoded = is_file($path) ? json_decode((string) file_get_contents($path), true) : null;

        return is_array($decoded) ? $decoded : [];
    }
}
