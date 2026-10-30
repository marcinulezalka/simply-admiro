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
 * Konfiguracja wdrożenia z `infrastructure/deploy.env` (albo produkcja z pliku projektu, ProjectConfig): serwer SSH,
 * katalog aplikacji, PHP i Composer na serwerze, Composer lokalny i token GitHub.
 */
final readonly class DeployConfig
{
    public const string DEFAULT_FILE = 'infrastructure/deploy.env';

    public const string SSH = 'DEPLOY_SSH';

    public const string PATH = 'DEPLOY_PATH';

    public const string PHP = 'DEPLOY_PHP';

    public const string REMOTE_COMPOSER = 'REMOTE_COMPOSER';

    public const string LOCAL_COMPOSER = 'LOCAL_COMPOSER';

    public const string GITHUB_TOKEN = 'GITHUB_TOKEN';

    private const string DEFAULT_PHP = 'php84';

    /**
     * @param  list<string>|null  $localComposer  Polecenie Composera na komputerze (np. `php composer.phar`);
     *                                            null — `composer` (albo COMPOSER_BINARY, gdy uruchomiono ze skryptu composera).
     */
    public function __construct(
        public string $ssh,
        public string $path,
        public string $php = self::DEFAULT_PHP,
        public ?string $remoteComposer = null,
        public ?array $localComposer = null,
        public ?string $githubToken = null,
    ) {}

    /**
     * @throws DeployConfigException
     */
    public static function fromFile(string $path, string $name): self
    {
        $values = EnvFile::read($path, $name);
        $missing = array_values(array_filter([self::SSH, self::PATH], fn(string $key): bool => ($values[$key] ?? '') === ''));

        if ($missing !== []) {
            throw MissingConfigException::keys($name, $missing);
        }

        return self::fromValues($values);
    }

    /**
     * Konfiguracja z wartości już wczytanych i sprawdzonych (SSH i PATH niepuste), np. z pliku projektu (ProjectConfig).
     *
     * @param  array<string, string>  $values
     */
    public static function fromValues(array $values): self
    {
        $local = preg_split('/\s+/', $values[self::LOCAL_COMPOSER] ?? '', -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return new self(
            ssh: $values[self::SSH] ?? '',
            path: $values[self::PATH] ?? '',
            php: self::optional($values, self::PHP) ?? self::DEFAULT_PHP,
            remoteComposer: self::optional($values, self::REMOTE_COMPOSER),
            localComposer: $local === [] ? null : $local,
            githubToken: self::optional($values, self::GITHUB_TOKEN),
        );
    }

    /**
     * @param  array<string, string>  $values
     */
    private static function optional(array $values, string $key): ?string
    {
        $value = $values[$key] ?? '';

        return $value === '' ? null : $value;
    }
}
