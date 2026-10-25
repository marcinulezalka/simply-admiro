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
 * Skrypt wysyłany na serwer przez `ssh … 'bash -s'` (standardowe wejście, więc token nie trafia do listy procesów):
 * eksporty z konfiguracji, przejście do katalogu aplikacji, `resources/deploy/remote.sh`, a przy onboardingu
 * zakładanie kont. Wdrożenie od zera eksportuje `DEPLOY_FRESH=1`, więc remote.sh migruje przez `migrate:fresh`. Kroki onboardingu używają `step` i `$php_bin` zdefiniowanych w remote.sh.
 */
final readonly class RemoteScript
{
    public const string SERVER_SCRIPT = __DIR__ . '/../../../resources/deploy/remote.sh';

    public function __construct(
        private string $serverScript,
    ) {}

    /**
     * @throws DeployException
     */
    public static function fromPackage(): self
    {
        $script = is_file(self::SERVER_SCRIPT) ? file_get_contents(self::SERVER_SCRIPT) : false;

        if ($script === false) {
            throw new DeployException('Brak skryptu serwerowego ' . self::SERVER_SCRIPT . '.');
        }

        return new self($script);
    }

    /**
     * @param  list<OnboardingAccount>  $accounts  Konta onboardingu (puste przy zwykłym wdrożeniu).
     * @param  bool  $freshDatabase  Czy skrypt serwerowy czyści bazę (`migrate:fresh`) zamiast zwykłych migracji.
     */
    public function build(DeployConfig $config, array $accounts = [], bool $freshDatabase = false): string
    {
        $lines = ['export DEPLOY_PHP=' . ShellArgument::quote($config->php) . ' GITHUB_TOKEN=' . ShellArgument::quote($config->githubToken ?? '')];

        if ($config->remoteComposer !== null) {
            $lines[] = 'export COMPOSER_BIN=' . ShellArgument::quote($config->remoteComposer);
        }

        if ($freshDatabase) {
            $lines[] = 'export DEPLOY_FRESH=1';
        }

        $lines[] = 'cd ' . ShellArgument::path($config->path) . ' || exit 1';
        $lines[] = rtrim($this->serverScript);

        foreach ($accounts as $account) {
            $lines[] = 'step ' . ShellArgument::quote("Onboarding: konto {$account->email} ({$account->zone})");
            $lines[] = '"$php_bin" artisan ' . ShellArgument::join($account->artisanArguments());
        }

        return implode("\n", $lines) . "\n";
    }
}
