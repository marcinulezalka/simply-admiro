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
 * Zewnętrzne pakiety Composera zainstalowane w aplikacji (`vendor/composer/installed.json`) do strony licencji:
 * bez pakietów deweloperskich i pakietów z repozytoriów `path` (własne paczki aplikacji), posortowane po nazwie.
 */
final readonly class InstalledPackages
{
    public function __construct(
        private string $installedJsonPath,
    ) {}

    public static function forApplication(): self
    {
        return new self(base_path('vendor/composer/installed.json'));
    }

    /**
     * @return list<array{name: string, version: string, licenses: list<string>, description: string, homepage: ?string}>
     */
    public function all(): array
    {
        $installed = is_file($this->installedJsonPath) ? json_decode((string) file_get_contents($this->installedJsonPath), true) : null;

        if (! is_array($installed) || ! is_array($installed['packages'] ?? null)) {
            return [];
        }

        $devNames = is_array($installed['dev-package-names'] ?? null) ? $installed['dev-package-names'] : [];
        $packages = [];

        foreach ($installed['packages'] as $package) {
            if (! is_array($package) || ! is_string($package['name'] ?? null) || in_array($package['name'], $devNames, true) || Arr::get($package, 'dist.type') === 'path') {
                continue;
            }

            $sourceUrl = Arr::get($package, 'source.url');
            $packages[] = [
                'name' => $package['name'],
                'version' => is_string($package['version'] ?? null) ? $package['version'] : '',
                'licenses' => array_values(array_filter(is_array($package['license'] ?? null) ? $package['license'] : [], is_string(...))),
                'description' => is_string($package['description'] ?? null) ? $package['description'] : '',
                'homepage' => is_string($package['homepage'] ?? null) ? $package['homepage'] : (is_string($sourceUrl) ? preg_replace('/\.git$/', '', $sourceUrl) : null),
            ];
        }

        usort($packages, static fn(array $first, array $second): int => strcmp($first['name'], $second['name']));

        return $packages;
    }
}
