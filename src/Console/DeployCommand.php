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

namespace Simplysmart\Tools\Console;

use Closure;
use Simplysmart\Tools\Deploy\DeployConfig;
use Simplysmart\Tools\Deploy\DeployException;
use Simplysmart\Tools\Deploy\DeployMode;
use Simplysmart\Tools\Deploy\OnboardingAccount;
use Simplysmart\Tools\Deploy\OnboardingConfig;
use Simplysmart\Tools\Deploy\RemoteScript;
use Simplysmart\Tools\Deploy\RepositoryCheck;
use Simplysmart\Tools\PrivatePackages\PrivatePackages;
use Simplysmart\Tools\Process\ProcessException;
use Simplysmart\Tools\Process\ProcessRunner;

/**
 * Polecenia `simplysmart deploy` i `simplysmart deploy:onboarding`, uruchamiane na komputerze deweloperskim:
 * sprawdza repozytoria, buduje manifest wdrożeniowy (`private:install --deploy`), wysyła go na serwer (scp)
 * i uruchamia tam skrypt serwerowy przez ssh; onboarding dopisuje zakładanie kont. `--dry-run` tylko sprawdza
 * konfigurację i repozytoria oraz pokazuje plan.
 */
final readonly class DeployCommand implements Command
{
    private const string DRY_RUN = '--dry-run';

    private const string CONFIG = '--config=';

    private const string ONBOARDING = '--onboarding=';

    private const string ROOT = '--root=';

    /**
     * @param  Closure(list<string>|null): Command  $lockCommand  Polecenie manifestu wdrożeniowego dla danego
     *                                                           polecenia Composera (LOCAL_COMPOSER).
     */
    public function __construct(
        private DeployMode $mode,
        private ProcessRunner $processes,
        private RepositoryCheck $repositories,
        private RemoteScript $script,
        private Closure $lockCommand,
    ) {}

    public function name(): string
    {
        return $this->mode->commandName();
    }

    public function description(): string
    {
        return $this->mode->description();
    }

    public function run(array $arguments, string $workingDirectory, $stdout, $stderr): int
    {
        $root = $workingDirectory;
        $config = DeployConfig::DEFAULT_FILE;
        $onboarding = OnboardingConfig::DEFAULT_FILE;
        $dryRun = false;

        foreach ($arguments as $argument) {
            if ($argument === self::DRY_RUN) {
                $dryRun = true;
            } elseif (str_starts_with($argument, self::CONFIG)) {
                $config = substr($argument, strlen(self::CONFIG));
            } elseif ($this->mode === DeployMode::Onboarding && str_starts_with($argument, self::ONBOARDING)) {
                $onboarding = substr($argument, strlen(self::ONBOARDING));
            } elseif (str_starts_with($argument, self::ROOT)) {
                $root = substr($argument, strlen(self::ROOT));
            } else {
                fwrite($stderr, "Nieznana opcja: {$argument}\n" . $this->usage() . "\n");

                return 1;
            }
        }

        try {
            $deploy = DeployConfig::fromFile(self::path($root, $config), $config);
            $accounts = $this->mode === DeployMode::Onboarding
                ? OnboardingConfig::fromFile(self::path($root, $onboarding), $onboarding)->accounts
                : [];

            if (! $this->repositoriesReady($root, $stdout, $stderr)) {
                return 1;
            }

            if ($dryRun) {
                fwrite($stdout, $this->plan($deploy, $accounts));

                return 0;
            }

            return $this->deploy($root, $deploy, $accounts, $stdout, $stderr);
        } catch (DeployException|ProcessException $exception) {
            fwrite($stderr, $exception->getMessage() . "\n");

            return 1;
        }
    }

    /**
     * @param  list<OnboardingAccount>  $accounts
     * @param  resource  $stdout
     * @param  resource  $stderr
     *
     * @throws DeployException|ProcessException
     */
    private function deploy(string $root, DeployConfig $config, array $accounts, $stdout, $stderr): int
    {
        fwrite($stdout, "==> Tworzę manifest wdrożeniowy\n");
        $lock = ($this->lockCommand)($config->localComposer)->run([PrivatePackagesCommand::DEPLOY, self::ROOT . $root], $root, $stdout, $stderr);

        if ($lock !== 0) {
            return $lock;
        }

        $files = [self::path($root, PrivatePackages::DEPLOY_MANIFEST), self::path($root, PrivatePackages::DEPLOY_LOCK)];

        foreach ($files as $file) {
            if (! is_file($file)) {
                throw new DeployException('Brak ' . basename($file) . ' — manifest wdrożeniowy nie powstał.');
            }
        }

        fwrite($stdout, "==> Wysyłam pliki wdrożeniowe na {$config->ssh}:{$config->path}\n");

        if ($this->processes->run(['scp', '-q', ...$files, "{$config->ssh}:{$config->path}/"]) !== 0) {
            throw new DeployException("Nie udało się wysłać plików na {$config->ssh}.");
        }

        fwrite($stdout, "==> Uruchamiam wdrożenie na serwerze\n");
        $exitCode = $this->processes->run(['ssh', $config->ssh, 'bash -s'], $this->script->build($config, $accounts));

        if ($exitCode !== 0) {
            fwrite($stderr, "Wdrożenie na serwerze zakończyło się błędem (kod {$exitCode}).\n");

            return $exitCode;
        }

        fwrite($stdout, "==> Gotowe\n");

        return 0;
    }

    /**
     * @param  resource  $stdout
     * @param  resource  $stderr
     */
    private function repositoriesReady(string $root, $stdout, $stderr): bool
    {
        fwrite($stdout, "==> Sprawdzam, czy wszystko jest wypchnięte\n");
        $report = $this->repositories->check($root);

        foreach ($report->unfetched as $name) {
            fwrite($stderr, "Uwaga: nie udało się pobrać stanu z origin dla {$name} — sprawdzam według ostatniego pobrania.\n");
        }

        foreach ($report->uncommitted as $name) {
            fwrite($stderr, "Uwaga: {$name} ma niezacommitowane zmiany — nie trafią na serwer.\n");
        }

        if ($report->unpushed !== []) {
            fwrite($stderr, 'Niewypchnięte commity: ' . implode(', ', $report->unpushed) . ". Zrób git push i uruchom ponownie.\n");

            return false;
        }

        return true;
    }

    /**
     * @param  list<OnboardingAccount>  $accounts
     */
    private function plan(DeployConfig $config, array $accounts): string
    {
        $lines = [
            "Serwer: {$config->ssh}, katalog: {$config->path}, PHP: {$config->php}.",
            'Kroki: manifest wdrożeniowy, scp ' . PrivatePackages::DEPLOY_MANIFEST . ' i ' . PrivatePackages::DEPLOY_LOCK . ', skrypt serwerowy przez ssh.',
        ];

        foreach ($accounts as $account) {
            $roles = $account->roles === [] ? 'bez ról' : 'role: ' . implode(', ', $account->roles);
            $lines[] = "Konto: {$account->email} ({$account->name}), strefa {$account->zone}, {$roles}.";
        }

        $lines[] = 'Tryb --dry-run: nic nie zostało wysłane ani uruchomione na serwerze.';

        return implode("\n", $lines) . "\n";
    }

    private function usage(): string
    {
        $onboarding = $this->mode === DeployMode::Onboarding ? ' [' . self::ONBOARDING . OnboardingConfig::DEFAULT_FILE . ']' : '';

        return 'Użycie: simplysmart ' . $this->name() . ' [' . self::DRY_RUN . '] [' . self::CONFIG . DeployConfig::DEFAULT_FILE . "]{$onboarding} [" . self::ROOT . '<katalog>]';
    }

    private static function path(string $root, string $file): string
    {
        return str_starts_with($file, '/') ? $file : "{$root}/{$file}";
    }
}
