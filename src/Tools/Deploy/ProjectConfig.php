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
 * Konfiguracja wdrożenia projektu z `infrastructure/deploy-vendors/<projekt>.env` (katalog ignorowany przez git):
 * przed-produkcja — katalog projektu na tym komputerze (`PREPRODUCTION_PATH`, PHP i Composer do skryptu
 * serwerowego) — i produkcja z tych samych kluczy co DeployConfig (`DEPLOY_SSH`, `DEPLOY_PATH`…). Konta pierwszej
 * instalacji projektu są w `<projekt>.onboarding.env` obok. Wzór: `infrastructure/deploy-vendor.env.example`.
 */
final readonly class ProjectConfig
{
    public const string DIRECTORY = 'infrastructure/deploy-vendors';

    public const string EXAMPLE = 'infrastructure/deploy-vendor.env.example';

    public const string PREPRODUCTION_PATH = 'PREPRODUCTION_PATH';

    public const string PREPRODUCTION_PHP = 'PREPRODUCTION_PHP';

    public const string PREPRODUCTION_COMPOSER = 'PREPRODUCTION_COMPOSER';

    private const string EXTENSION = '.env';

    private const string ONBOARDING_SUFFIX = '.onboarding.env';

    private const string DEFAULT_PHP = 'php';

    /** Nazwa projektu: małe litery, cyfry, kropki, myślniki i podkreślenia — bez ścieżek. */
    private const string NAME = '/^[a-z0-9][a-z0-9._-]*$/';

    public function __construct(
        public string $name,
        public DeployConfig $production,
        public string $preproductionPath,
        public string $preproductionPhp = self::DEFAULT_PHP,
        public ?string $preproductionComposer = null,
    ) {}

    /**
     * @throws DeployConfigException Gdy nazwa jest niepoprawna, projektu nie ma albo brakuje wymaganych kluczy.
     */
    public static function load(string $root, string $name): self
    {
        if (preg_match(self::NAME, $name) !== 1 || str_ends_with($name . self::EXTENSION, self::ONBOARDING_SUFFIX)) {
            throw new DeployConfigException("Niepoprawna nazwa projektu: {$name}.");
        }

        $file = self::DIRECTORY . '/' . $name . self::EXTENSION;
        $path = "{$root}/{$file}";

        if (! is_file($path)) {
            $available = self::available($root);

            throw new MissingConfigException("Brak projektu {$name} ({$file}). " . ($available === []
                ? 'Utwórz plik według ' . self::EXAMPLE . '.'
                : 'Dostępne projekty: ' . implode(', ', $available) . '.'));
        }

        $values = EnvFile::read($path, $file);
        $missing = array_values(array_filter([DeployConfig::SSH, DeployConfig::PATH, self::PREPRODUCTION_PATH], fn(string $key): bool => ($values[$key] ?? '') === ''));

        if ($missing !== []) {
            throw new MissingConfigException("Brak wymaganych wartości w {$file}: " . implode(', ', $missing) . '. Wzór: ' . self::EXAMPLE . '.');
        }

        if (! str_starts_with($values[self::PREPRODUCTION_PATH], '/')) {
            throw new DeployConfigException("{$file}: " . self::PREPRODUCTION_PATH . ' musi być ścieżką bezwzględną.');
        }

        return new self(
            name: $name,
            production: DeployConfig::fromValues($values),
            preproductionPath: rtrim($values[self::PREPRODUCTION_PATH], '/'),
            preproductionPhp: ($values[self::PREPRODUCTION_PHP] ?? '') !== '' ? $values[self::PREPRODUCTION_PHP] : self::DEFAULT_PHP,
            preproductionComposer: ($values[self::PREPRODUCTION_COMPOSER] ?? '') !== '' ? $values[self::PREPRODUCTION_COMPOSER] : null,
        );
    }

    /**
     * Projekty z plikami w DIRECTORY (bez plików onboardingu), posortowane.
     *
     * @return list<string>
     */
    public static function available(string $root): array
    {
        $names = [];

        foreach (glob("{$root}/" . self::DIRECTORY . '/*' . self::EXTENSION) ?: [] as $file) {
            if (! str_ends_with($file, self::ONBOARDING_SUFFIX)) {
                $names[] = basename($file, self::EXTENSION);
            }
        }

        sort($names);

        return $names;
    }

    /**
     * Plik kont pierwszej instalacji projektu, względem katalogu projektu bazowego.
     */
    public function onboardingFile(): string
    {
        return self::DIRECTORY . '/' . $this->name . self::ONBOARDING_SUFFIX;
    }
}
