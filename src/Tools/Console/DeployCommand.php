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

namespace Simplysmart\Admiro\Tools\Console;

use Closure;
use Simplysmart\Admiro\Tools\Deploy\DeployConfig;
use Simplysmart\Admiro\Tools\Deploy\DeployException;
use Simplysmart\Admiro\Tools\Deploy\DeployMode;
use Simplysmart\Admiro\Tools\Deploy\OnboardingAccount;
use Simplysmart\Admiro\Tools\Deploy\OnboardingConfig;
use Simplysmart\Admiro\Tools\Deploy\RemoteScript;
use Simplysmart\Admiro\Tools\Deploy\RepositoryCheck;
use Simplysmart\Admiro\Tools\PrivatePackages\PrivatePackages;
use Simplysmart\Admiro\Tools\Process\ProcessException;
use Simplysmart\Admiro\Tools\Process\ProcessRunner;

/**
 * Polecenia `simplysmart deploy`, `simplysmart deploy:onboarding` i `simplysmart deploy:fresh`, uruchamiane na
 * komputerze deweloperskim: sprawdza repozytoria, buduje manifest wdrożeniowy (`private:install --deploy`), wysyła go
 * na serwer (scp) i uruchamia tam skrypt serwerowy przez ssh. Środowiska nie wysyła: skrypt serwerowy kopiuje
 * `.env.production` leżący na serwerze (w katalogu aplikacji) jako `.env` (APP_KEY serwera zostaje, gdy plik go nie
 * podaje; bez pliku `.env` serwera bez zmian), więc lokalny plik projektu nie nadpisze środowiska produkcji. Onboarding dopisuje zakładanie kont, a `deploy:fresh`
 * dodatkowo czyści bazę (`migrate:fresh`) i wymaga `--confirm=<DEPLOY_SSH>`. `--dry-run` tylko sprawdza
 * konfigurację i repozytoria oraz pokazuje plan. `--with-push` najpierw wypycha repozytoria z niewypchniętymi commitami
 * (paczki z `packages/*\/*`, także prywatne, potem projekt; RepositoryCheck::push()), a dopiero potem wdraża; z `--dry-run`
 * tylko pokazuje, co zostałoby wypchnięte.
 */
final readonly class DeployCommand implements Command
{
    private const string DRY_RUN = '--dry-run';

    private const string WITH_PUSH = '--with-push';

    private const string CONFIG = '--config=';

    private const string ONBOARDING = '--onboarding=';

    private const string CONFIRM = '--confirm=';

    private const string ROOT = '--root=';

    /** Plik środowiska produkcji na serwerze (w katalogu aplikacji), kopiowany jako `.env` (resources/deploy/remote.sh). */
    private const string PRODUCTION_ENV = '.env.production';

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
        $withPush = false;
        $confirmation = null;

        foreach ($arguments as $argument) {
            if ($argument === self::DRY_RUN) {
                $dryRun = true;
            } elseif ($argument === self::WITH_PUSH) {
                $withPush = true;
            } elseif (str_starts_with($argument, self::CONFIG)) {
                $config = substr($argument, strlen(self::CONFIG));
            } elseif ($this->mode->createsAccounts() && str_starts_with($argument, self::ONBOARDING)) {
                $onboarding = substr($argument, strlen(self::ONBOARDING));
            } elseif ($this->mode->resetsDatabase() && str_starts_with($argument, self::CONFIRM)) {
                $confirmation = substr($argument, strlen(self::CONFIRM));
            } elseif (str_starts_with($argument, self::ROOT)) {
                $root = substr($argument, strlen(self::ROOT));
            } else {
                fwrite($stderr, "Nieznana opcja: {$argument}\n" . $this->usage() . "\n");

                return 1;
            }
        }

        try {
            $deploy = DeployConfig::fromFile(self::path($root, $config), $config);
            $accounts = $this->mode->createsAccounts()
                ? OnboardingConfig::fromFile(self::path($root, $onboarding), $onboarding)->accounts
                : [];

            if (! $dryRun) {
                $this->assertConfirmed($deploy, $confirmation);
            }

            if ($withPush && ! $dryRun) {
                $this->push($root, $stdout);
            }

            if (! $this->repositoriesReady($root, $stdout, $stderr, $withPush && $dryRun)) {
                return 1;
            }

            if ($dryRun) {
                fwrite($stdout, $this->plan($root, $deploy, $accounts));

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
        $exitCode = $this->processes->run(['ssh', $config->ssh, 'bash -s'], $this->script->build($config, $accounts, $this->mode->resetsDatabase()));

        if ($exitCode !== 0) {
            fwrite($stderr, "Wdrożenie na serwerze zakończyło się błędem (kod {$exitCode}).\n");

            return $exitCode;
        }

        fwrite($stdout, "==> Gotowe\n");

        return 0;
    }

    /**
     * Wdrożenie czyszczące bazę rusza tylko z `--confirm=` równym DEPLOY_SSH, więc nie da się go uruchomić
     * przypadkiem ani na serwerze z innej konfiguracji.
     *
     * @throws DeployException Gdy brak potwierdzenia albo wskazuje inny serwer.
     */
    private function assertConfirmed(DeployConfig $config, ?string $confirmation): void
    {
        if (! $this->mode->resetsDatabase() || $confirmation === $config->ssh) {
            return;
        }

        throw new DeployException("{$this->name()} usunie wszystkie tabele i dane bazy na {$config->ssh}:{$config->path}. "
            . 'Potwierdź, podając ' . self::CONFIRM . "{$config->ssh} (podgląd: " . self::DRY_RUN . ').');
    }

    /**
     * @param  resource  $stdout
     * @param  resource  $stderr
     */
    private function repositoriesReady(string $root, $stdout, $stderr, bool $pushPlanned = false): bool
    {
        fwrite($stdout, "==> Sprawdzam, czy wszystko jest wypchnięte\n");
        $report = $this->repositories->check($root);

        foreach ($report->unfetched as $name) {
            fwrite($stderr, "Uwaga: nie udało się pobrać stanu z origin dla {$name} — sprawdzam według ostatniego pobrania.\n");
        }

        foreach ($report->uncommitted as $name) {
            fwrite($stderr, "Uwaga: {$name} ma niezacommitowane zmiany — nie trafią na serwer.\n");
        }

        if ($report->unpushed !== [] && $pushPlanned) {
            fwrite($stdout, 'Do wypchnięcia (' . self::WITH_PUSH . '): ' . implode(', ', $report->unpushed) . ".\n");
        } elseif ($report->unpushed !== []) {
            fwrite($stderr, 'Niewypchnięte commity: ' . implode(', ', $report->unpushed) . '. Zrób git push (albo uruchom z ' . self::WITH_PUSH . ") i uruchom ponownie.\n");

            return false;
        }

        return true;
    }

    /**
     * @param  resource  $stdout
     *
     * @throws DeployException|ProcessException
     */
    private function push(string $root, $stdout): void
    {
        fwrite($stdout, "==> Wypycham repozytoria (paczki, także prywatne, potem projekt)\n");
        $pushed = $this->repositories->push($root);
        fwrite($stdout, $pushed === [] ? "Nic do wypchnięcia.\n" : 'Wypchnięte: ' . implode(', ', $pushed) . ".\n");
    }

    /**
     * @param  list<OnboardingAccount>  $accounts
     */
    private function plan(string $root, DeployConfig $config, array $accounts): string
    {
        $lines = [
            "Serwer: {$config->ssh}, katalog: {$config->path}, PHP: {$config->php}.",
            'Kroki: manifest wdrożeniowy, scp ' . PrivatePackages::DEPLOY_MANIFEST . ' i ' . PrivatePackages::DEPLOY_LOCK . ', skrypt serwerowy przez ssh.',
            'Środowisko: ' . self::PRODUCTION_ENV . " z serwera ({$config->path}) zostanie skopiowany jako .env (APP_KEY serwera zostaje, gdy plik go nie podaje; bez pliku .env bez zmian); lokalny plik nie jest wysyłany.",
        ];

        if ($this->mode->resetsDatabase()) {
            $lines[] = 'Baza: migrate:fresh — wszystkie tabele i dane na serwerze zostaną usunięte (uruchomienie: ' . self::CONFIRM . "{$config->ssh}).";
        }

        foreach ($accounts as $account) {
            $roles = $account->roles === [] ? 'bez ról' : 'role: ' . implode(', ', $account->roles);
            $lines[] = "Konto: {$account->email} ({$account->name}), strefa {$account->zone}, {$roles}.";
        }

        $lines[] = 'Tryb --dry-run: nic nie zostało wysłane ani uruchomione na serwerze.';

        return implode("\n", $lines) . "\n";
    }

    private function usage(): string
    {
        $onboarding = $this->mode->createsAccounts() ? ' [' . self::ONBOARDING . OnboardingConfig::DEFAULT_FILE . ']' : '';
        $confirm = $this->mode->resetsDatabase() ? ' ' . self::CONFIRM . '<DEPLOY_SSH>' : '';

        return 'Użycie: simplysmart ' . $this->name() . " [" . self::DRY_RUN . '] [' . self::WITH_PUSH . ']' . $confirm . ' [' . self::CONFIG . DeployConfig::DEFAULT_FILE . "]{$onboarding} [" . self::ROOT . '<katalog>]';
    }

    private static function path(string $root, string $file): string
    {
        return str_starts_with($file, '/') ? $file : "{$root}/{$file}";
    }
}
