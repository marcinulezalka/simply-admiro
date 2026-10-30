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
use Simplysmart\Admiro\Tools\Deploy\MissingConfigException;
use Simplysmart\Admiro\Tools\Deploy\OnboardingAccount;
use Simplysmart\Admiro\Tools\Deploy\OnboardingConfig;
use Simplysmart\Admiro\Tools\Deploy\ProjectConfig;
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
 *
 * Z nazwą projektu (`deploy <projekt>`, konfiguracja ProjectConfig z `infrastructure/deploy-vendors/<projekt>.env`)
 * polecenie działa w projekcie bazowym (z paczkami w `packages/`): po sprawdzeniu, że poprawki są wypchnięte w jego
 * repozytorium i repozytoriach paczek (także prywatnych), buduje świeży manifest (stary jest usuwany, więc nie da się
 * wdrożyć nieaktualnego), aktualizuje przed-produkcję — katalog projektu na tym komputerze — tym samym skryptem
 * serwerowym (composer z manifestu bez narzędzi deweloperskich, migracje, seedy, cache; `.env` przed-produkcji bez
 * zmian), a potem pyta, czy wdrożyć też produkcję (`--production` — bez pytania, `--no-production` — tylko
 * przed-produkcja; bez terminala odpowiedź to „nie”). Błąd przed-produkcji zatrzymuje wdrożenie. Onboarding i fresh
 * biorą konta z `<projekt>.onboarding.env` i zakładają je w obu środowiskach; fresh czyści bazę obu i wymaga
 * `--confirm=<DEPLOY_SSH>`.
 */
final readonly class DeployCommand implements Command
{
    private const string DRY_RUN = '--dry-run';

    private const string WITH_PUSH = '--with-push';

    private const string CONFIG = '--config=';

    private const string ONBOARDING = '--onboarding=';

    private const string CONFIRM = '--confirm=';

    private const string ROOT = '--root=';

    private const string PRODUCTION = '--production';

    private const string NO_PRODUCTION = '--no-production';

    /** Plik środowiska produkcji na serwerze (w katalogu aplikacji), kopiowany jako `.env` (resources/deploy/remote.sh). */
    private const string PRODUCTION_ENV = '.env.production';

    /**
     * @param  Closure(list<string>|null): Command  $lockCommand  Polecenie manifestu wdrożeniowego dla danego
     *                                                           polecenia Composera (LOCAL_COMPOSER).
     * @param  Closure(string): bool  $confirm  Pytanie tak/nie do osoby wdrażającej (bin/simplysmart: terminal;
     *                                          bez terminala — „nie”).
     */
    public function __construct(
        private DeployMode $mode,
        private ProcessRunner $processes,
        private RepositoryCheck $repositories,
        private RemoteScript $script,
        private Closure $lockCommand,
        private Closure $confirm,
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
        $onboarding = null;
        $dryRun = false;
        $withPush = false;
        $confirmation = null;
        $project = null;
        $production = null;

        foreach ($arguments as $argument) {
            if ($project === null && ! str_starts_with($argument, '-')) {
                $project = $argument;
            } elseif ($argument === self::PRODUCTION || $argument === self::NO_PRODUCTION) {
                $production = $argument === self::PRODUCTION;
            } elseif ($argument === self::DRY_RUN) {
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

        if ($project === null && $production !== null) {
            fwrite($stderr, 'Opcje ' . self::PRODUCTION . ' i ' . self::NO_PRODUCTION . " działają tylko z nazwą projektu.\n" . $this->usage() . "\n");

            return 1;
        }

        try {
            if ($project !== null) {
                return $this->runProject(ProjectConfig::load($root, $project), $root, $onboarding, $dryRun, $withPush, $confirmation, $production, $stdout, $stderr);
            }

            $deploy = $this->config($root, $config);
            $onboarding ??= OnboardingConfig::DEFAULT_FILE;
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
                fwrite($stdout, $this->plan($deploy, $accounts));

                return 0;
            }

            $lock = $this->manifest($root, $deploy, $stdout, $stderr);

            return $lock !== 0 ? $lock : $this->production($root, $deploy, $accounts, $stdout, $stderr);
        } catch (DeployException|ProcessException $exception) {
            fwrite($stderr, $exception->getMessage() . "\n");

            return 1;
        }
    }

    /**
     * Wdrożenie projektu: repozytoria, świeży manifest, przed-produkcja, pytanie i produkcja.
     *
     * @param  bool|null  $production  true — produkcja bez pytania, false — bez produkcji, null — pytanie.
     * @param  resource  $stdout
     * @param  resource  $stderr
     *
     * @throws DeployException|ProcessException
     */
    private function runProject(ProjectConfig $project, string $root, ?string $onboarding, bool $dryRun, bool $withPush, ?string $confirmation, ?bool $production, $stdout, $stderr): int
    {
        $onboarding ??= $project->onboardingFile();
        $accounts = $this->mode->createsAccounts()
            ? OnboardingConfig::fromFile(self::path($root, $onboarding), $onboarding)->accounts
            : [];

        if (! $dryRun) {
            $this->assertConfirmed($project->production, $confirmation, $project);
        }

        if ($withPush && ! $dryRun) {
            $this->push($root, $stdout);
        }

        if (! $this->repositoriesReady($root, $stdout, $stderr, $withPush && $dryRun)) {
            return 1;
        }

        if ($dryRun) {
            fwrite($stdout, $this->plan($project->production, $accounts, $this->projectPlan($project, $production)));

            return 0;
        }

        foreach ($this->manifestFiles($root) as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        $lock = $this->manifest($root, $project->production, $stdout, $stderr);

        if ($lock !== 0) {
            return $lock;
        }

        $preproduction = $this->preproduction($root, $project, $accounts, $stdout, $stderr);

        if ($preproduction !== 0) {
            return $preproduction;
        }

        $target = "{$project->production->ssh}:{$project->production->path}";

        if (! ($production ?? ($this->confirm)("Wdrożyć też na produkcję {$target}? [t/N] "))) {
            fwrite($stdout, "==> Produkcja pominięta — zaktualizowano tylko przed-produkcję\n");

            return 0;
        }

        return $this->production($root, $project->production, $accounts, $stdout, $stderr);
    }

    /**
     * Manifest wdrożeniowy w katalogu projektu (`private:install --deploy`).
     *
     * @param  resource  $stdout
     * @param  resource  $stderr
     *
     * @throws DeployException Gdy plików manifestu nie ma.
     */
    private function manifest(string $root, DeployConfig $config, $stdout, $stderr): int
    {
        fwrite($stdout, "==> Tworzę manifest wdrożeniowy\n");
        $lock = ($this->lockCommand)($config->localComposer)->run([PrivatePackagesCommand::DEPLOY, self::ROOT . $root], $root, $stdout, $stderr);

        if ($lock !== 0) {
            return $lock;
        }

        foreach ($this->manifestFiles($root) as $file) {
            if (! is_file($file)) {
                throw new DeployException('Brak ' . basename($file) . ' — manifest wdrożeniowy nie powstał (wdrożenie projektu uruchamiaj w projekcie bazowym z paczkami w packages/).');
            }
        }

        return 0;
    }

    /**
     * Przed-produkcja projektu: manifest do jej katalogu i skrypt serwerowy w nim (`bash -s`, `.env` bez zmian).
     *
     * @param  list<OnboardingAccount>  $accounts
     * @param  resource  $stdout
     * @param  resource  $stderr
     *
     * @throws DeployException|ProcessException
     */
    private function preproduction(string $root, ProjectConfig $project, array $accounts, $stdout, $stderr): int
    {
        $directory = $project->preproductionPath;

        if (! is_file("{$directory}/artisan") || ! is_dir("{$directory}/.git")) {
            throw new DeployException("Przed-produkcja {$directory} nie jest projektem (brak artisan albo .git) — popraw " . ProjectConfig::PREPRODUCTION_PATH . '.');
        }

        fwrite($stdout, "==> Kopiuję manifest do przed-produkcji {$directory}\n");

        foreach ($this->manifestFiles($root) as $file) {
            if (! copy($file, "{$directory}/" . basename($file))) {
                throw new DeployException('Nie udało się skopiować ' . basename($file) . " do {$directory}.");
            }
        }

        fwrite($stdout, "==> Aktualizuję przed-produkcję\n");
        $exitCode = $this->processes->run(['bash', '-s'], $this->script->buildPreproduction($project, $accounts, $this->mode->resetsDatabase()), $directory);

        if ($exitCode !== 0) {
            fwrite($stderr, "Aktualizacja przed-produkcji zakończyła się błędem (kod {$exitCode}) — produkcja nie została wdrożona.\n");

            return $exitCode;
        }

        return 0;
    }

    /**
     * Produkcja: scp manifestu i skrypt serwerowy przez ssh.
     *
     * @param  list<OnboardingAccount>  $accounts
     * @param  resource  $stdout
     * @param  resource  $stderr
     *
     * @throws DeployException|ProcessException
     */
    private function production(string $root, DeployConfig $config, array $accounts, $stdout, $stderr): int
    {
        fwrite($stdout, "==> Wysyłam pliki wdrożeniowe na {$config->ssh}:{$config->path}\n");

        if ($this->processes->run(['scp', '-q', ...$this->manifestFiles($root), "{$config->ssh}:{$config->path}/"]) !== 0) {
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
    private function assertConfirmed(DeployConfig $config, ?string $confirmation, ?ProjectConfig $project = null): void
    {
        if (! $this->mode->resetsDatabase() || $confirmation === $config->ssh) {
            return;
        }

        $preproduction = $project !== null ? "przed-produkcji {$project->preproductionPath} i " : '';

        throw new DeployException("{$this->name()} usunie wszystkie tabele i dane bazy {$preproduction}na {$config->ssh}:{$config->path}. "
            . 'Potwierdź, podając ' . self::CONFIRM . "{$config->ssh} (podgląd: " . self::DRY_RUN . ').');
    }

    /**
     * Konfiguracja bez projektu (`--config=`); brak domyślnego pliku podpowiada projekty z `deploy-vendors`.
     *
     * @throws DeployException
     */
    private function config(string $root, string $file): DeployConfig
    {
        try {
            return DeployConfig::fromFile(self::path($root, $file), $file);
        } catch (MissingConfigException $exception) {
            $projects = $file === DeployConfig::DEFAULT_FILE && ! is_file(self::path($root, $file)) ? ProjectConfig::available($root) : [];

            if ($projects === []) {
                throw $exception;
            }

            throw new DeployException('Brak ' . DeployConfig::DEFAULT_FILE . '. Wdrażaj projekt: simplysmart ' . $this->name() . ' <projekt> — dostępne: ' . implode(', ', $projects) . '.');
        }
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
     * @param  list<string>  $lines  Linie planu przed opisem serwera (np. przed-produkcja projektu).
     */
    private function plan(DeployConfig $config, array $accounts, array $lines = []): string
    {
        $lines = [
            ...$lines,
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

    /**
     * Plan przed-produkcji i produkcji projektu (przed opisem serwera).
     *
     * @return list<string>
     */
    private function projectPlan(ProjectConfig $project, ?bool $production): array
    {
        $lines = [
            "Projekt: {$project->name}. Manifest: świeży, z repozytoriów projektu bazowego i paczek (także prywatnych).",
            "Przed-produkcja: {$project->preproductionPath}, PHP: {$project->preproductionPhp}; skrypt serwerowy lokalnie (composer z manifestu bez narzędzi deweloperskich, migracje, seedy, cache); .env przed-produkcji bez zmian.",
            'Produkcja: ' . match ($production) {
                true => 'bez pytania (' . self::PRODUCTION . ').',
                false => 'pominięta (' . self::NO_PRODUCTION . ').',
                null => 'po potwierdzeniu pytania „Wdrożyć też na produkcję?” (bez terminala: nie).',
            },
        ];

        if ($this->mode->resetsDatabase()) {
            $lines[] = "Baza przed-produkcji: migrate:fresh — wszystkie tabele i dane w {$project->preproductionPath} zostaną usunięte.";
        }

        return $lines;
    }

    /**
     * @return list<string>
     */
    private function manifestFiles(string $root): array
    {
        return [self::path($root, PrivatePackages::DEPLOY_MANIFEST), self::path($root, PrivatePackages::DEPLOY_LOCK)];
    }

    private function usage(): string
    {
        $onboarding = $this->mode->createsAccounts() ? ' [' . self::ONBOARDING . OnboardingConfig::DEFAULT_FILE . ']' : '';
        $confirm = $this->mode->resetsDatabase() ? ' ' . self::CONFIRM . '<DEPLOY_SSH>' : '';

        return 'Użycie: simplysmart ' . $this->name() . ' [<projekt> [' . self::PRODUCTION . '|' . self::NO_PRODUCTION . ']] [' . self::DRY_RUN . '] [' . self::WITH_PUSH . ']' . $confirm . ' [' . self::CONFIG . DeployConfig::DEFAULT_FILE . "]{$onboarding} [" . self::ROOT . '<katalog>]';
    }

    private static function path(string $root, string $file): string
    {
        return str_starts_with($file, '/') ? $file : "{$root}/{$file}";
    }
}
