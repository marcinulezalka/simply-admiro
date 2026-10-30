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

namespace Simplysmart\Admiro\Tests\Tools\Console;

use Closure;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Simplysmart\Admiro\Tools\Console\Command;
use Simplysmart\Admiro\Tools\Console\DeployCommand;
use Simplysmart\Admiro\Tools\Deploy\DeployMode;
use Simplysmart\Admiro\Tools\Deploy\RemoteScript;
use Simplysmart\Admiro\Tools\Deploy\RepositoryCheck;
use Simplysmart\Admiro\Tools\PrivatePackages\RepositorySource;
use Simplysmart\Admiro\Tools\Process\ProcessRunner;

/**
 * Wdrożenie w katalogu tymczasowym: git, scp, ssh i bash (przed-produkcja) zastąpione zapisem wywołań, manifest —
 * atrapą polecenia, a pytanie o produkcję — atrapą z ustaloną odpowiedzią (testy nigdy nie czytają STDIN).
 */
final class DeployCommandTest extends TestCase
{
    private string $root;

    /**
     * @var list<array{0: list<string>, 1: string|null, 2: string|null}>
     */
    private array $processCalls = [];

    /**
     * @var list<list<string>>
     */
    private array $lockCalls = [];

    /**
     * @var list<string>
     */
    private array $unpushed = [];

    /** Kod wyjścia `git push` w atrapie procesów. */
    private int $pushExitCode = 0;

    /** Kod wyjścia skryptu przed-produkcji (`bash -s`) w atrapie procesów. */
    private int $preproductionExitCode = 0;

    /** Odpowiedź atrapy pytania o produkcję (testy nigdy nie czytają STDIN). */
    private bool $answer = false;

    /**
     * @var list<string>
     */
    private array $questions = [];

    /** Katalog przed-produkcji projektu testowego. */
    private string $preproduction;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/deploy-' . bin2hex(random_bytes(4));
        mkdir("{$this->root}/infrastructure", recursive: true);
        mkdir("{$this->root}/.git");
        mkdir("{$this->root}/packages/acme/core/.git", recursive: true);
        file_put_contents("{$this->root}/infrastructure/deploy.env", "DEPLOY_SSH=user@host\nDEPLOY_PATH=~/app.pl\nGITHUB_TOKEN='sekret'\n");
        file_put_contents("{$this->root}/infrastructure/deploy.onboarding.env", "ONBOARD_ACCOUNT_1_ZONE=admiro\nONBOARD_ACCOUNT_1_EMAIL=a@example.com\nONBOARD_ACCOUNT_1_NAME=\"Jan Kowalski\"\nONBOARD_ACCOUNT_1_ROLES=hero\n");

        $this->preproduction = "{$this->root}-staging";
        mkdir("{$this->preproduction}/.git", recursive: true);
        file_put_contents("{$this->preproduction}/artisan", '');
        mkdir("{$this->root}/infrastructure/deploy-vendors");
        file_put_contents("{$this->root}/infrastructure/deploy-vendors/shop.env", "PREPRODUCTION_PATH={$this->preproduction}\nPREPRODUCTION_PHP=php85\nDEPLOY_SSH=shop@prod\nDEPLOY_PATH=~/shop.pl\nGITHUB_TOKEN='sekret'\n");
        file_put_contents("{$this->root}/infrastructure/deploy-vendors/shop.onboarding.env", "ONBOARD_ACCOUNT_1_ZONE=shop\nONBOARD_ACCOUNT_1_EMAIL=s@example.com\nONBOARD_ACCOUNT_1_NAME=\"Sklep Admin\"\nONBOARD_ACCOUNT_1_ROLES=owner\n");
    }

    protected function tearDown(): void
    {
        exec('rm -rf ' . escapeshellarg($this->root) . ' ' . escapeshellarg($this->preproduction));
    }

    #[Test]
    public function projekt_aktualizuje_przed_produkcje_i_po_odmowie_nie_rusza_produkcji(): void
    {
        file_put_contents("{$this->root}/composer.deploy.lock", 'stary');

        [$exitCode, $stdout] = $this->runCommand(DeployMode::Deploy, ['shop']);

        $this->assertSame(0, $exitCode, $stdout);
        $this->assertSame([['--deploy', "--root={$this->root}"]], $this->lockCalls);
        $this->assertSame('{}', file_get_contents("{$this->preproduction}/composer.deploy.lock"), 'Przed-produkcja dostaje świeży manifest, nie stary lock.');
        $this->assertFileExists("{$this->preproduction}/composer.deploy.json");
        $bash = $this->callsOf('bash');
        $this->assertCount(1, $bash);
        $this->assertSame(['bash', '-s'], $bash[0][0]);
        $this->assertSame("export DEPLOY_PHP='php85' DEPLOY_KEEP_ENV=1\ncd '{$this->preproduction}' || exit 1\necho serwer\n", $bash[0][1]);
        $this->assertSame($this->preproduction, $bash[0][2]);
        $this->assertSame(['Wdrożyć też na produkcję shop@prod:~/shop.pl? [t/N] '], $this->questions);
        $this->assertSame([], $this->callsOf('scp'));
        $this->assertSame([], $this->callsOf('ssh'));
        $this->assertStringEndsWith("==> Produkcja pominięta — zaktualizowano tylko przed-produkcję\n", $stdout);
    }

    #[Test]
    public function projekt_po_potwierdzeniu_wdraza_produkcje_tym_samym_manifestem(): void
    {
        $this->answer = true;

        [$exitCode, $stdout] = $this->runCommand(DeployMode::Deploy, ['shop']);

        $this->assertSame(0, $exitCode, $stdout);
        $this->assertSame([['scp', '-q', "{$this->root}/composer.deploy.json", "{$this->root}/composer.deploy.lock", 'shop@prod:~/shop.pl/']], array_map(fn(array $call): array => $call[0], $this->callsOf('scp')));
        $ssh = $this->lastProcessCall();
        $this->assertSame(['ssh', 'shop@prod', 'bash -s'], $ssh[0]);
        $this->assertSame("export DEPLOY_PHP='php84' GITHUB_TOKEN='sekret'\ncd ~/'shop.pl' || exit 1\necho serwer\n", $ssh[1]);
        $this->assertStringEndsWith("==> Gotowe\n", $stdout);
    }

    #[Test]
    public function flagi_produkcji_zastepuja_pytanie(): void
    {
        [$withProduction] = $this->runCommand(DeployMode::Deploy, ['shop', '--production']);
        $this->assertSame(0, $withProduction);
        $this->assertCount(1, $this->callsOf('ssh'));

        [$withoutProduction] = $this->runCommand(DeployMode::Deploy, ['shop', '--no-production']);
        $this->assertSame(0, $withoutProduction);
        $this->assertCount(1, $this->callsOf('ssh'));
        $this->assertCount(2, $this->callsOf('bash'));
        $this->assertSame([], $this->questions);
    }

    #[Test]
    public function blad_przed_produkcji_zatrzymuje_produkcje(): void
    {
        $this->preproductionExitCode = 3;

        [$exitCode, , $stderr] = $this->runCommand(DeployMode::Deploy, ['shop', '--production']);

        $this->assertSame(3, $exitCode);
        $this->assertStringContainsString('Aktualizacja przed-produkcji zakończyła się błędem (kod 3) — produkcja nie została wdrożona.', $stderr);
        $this->assertSame([], $this->callsOf('scp'));
        $this->assertSame([], $this->questions);
    }

    #[Test]
    public function projekt_wymaga_wypchnietych_poprawek(): void
    {
        $this->unpushed = ["{$this->root}/packages/acme/core"];

        [$exitCode, , $stderr] = $this->runCommand(DeployMode::Deploy, ['shop', '--production']);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Niewypchnięte commity: packages/acme/core.', $stderr);
        $this->assertSame([], $this->lockCalls);
        $this->assertSame([], $this->callsOf('bash'));
    }

    #[Test]
    public function nieznany_albo_niepoprawny_projekt_to_czytelny_blad(): void
    {
        [$unknown, , $unknownError] = $this->runCommand(DeployMode::Deploy, ['sklep']);
        [$invalid, , $invalidError] = $this->runCommand(DeployMode::Deploy, ['../shop']);

        $this->assertSame(1, $unknown);
        $this->assertSame("Brak projektu sklep (infrastructure/deploy-vendors/sklep.env). Dostępne projekty: shop.\n", $unknownError);
        $this->assertSame(1, $invalid);
        $this->assertSame("Niepoprawna nazwa projektu: ../shop.\n", $invalidError);
        $this->assertSame([], $this->processCalls);
    }

    #[Test]
    public function bez_deploy_env_podpowiada_projekty(): void
    {
        unlink("{$this->root}/infrastructure/deploy.env");

        [$exitCode, , $stderr] = $this->runCommand(DeployMode::Deploy, []);

        $this->assertSame(1, $exitCode);
        $this->assertSame("Brak infrastructure/deploy.env. Wdrażaj projekt: simplysmart deploy <projekt> — dostępne: shop.\n", $stderr);
    }

    #[Test]
    public function flaga_produkcji_bez_projektu_jest_bledem(): void
    {
        [$exitCode, , $stderr] = $this->runCommand(DeployMode::Deploy, ['--production']);

        $this->assertSame(1, $exitCode);
        $this->assertStringStartsWith("Opcje --production i --no-production działają tylko z nazwą projektu.\n", $stderr);
        $this->assertSame([], $this->processCalls);
    }

    #[Test]
    public function onboarding_projektu_zaklada_konta_z_jego_pliku_w_obu_srodowiskach(): void
    {
        [$exitCode, $stdout] = $this->runCommand(DeployMode::Onboarding, ['shop', '--production']);

        $this->assertSame(0, $exitCode, $stdout);
        $account = "\"\$php_bin\" artisan 'shop:account:create' 's@example.com' 'Sklep Admin' '--role=owner' '--skip-existing'\n";
        $this->assertStringEndsWith($account, (string) $this->callsOf('bash')[0][1]);
        $this->assertStringEndsWith($account, (string) $this->lastProcessCall()[1]);
    }

    #[Test]
    public function fresh_projektu_wymaga_potwierdzenia_i_czysci_oba_srodowiska(): void
    {
        [$refused, , $stderr] = $this->runCommand(DeployMode::Fresh, ['shop', '--production']);

        $this->assertSame(1, $refused);
        $this->assertSame("deploy:fresh usunie wszystkie tabele i dane bazy przed-produkcji {$this->preproduction} i na shop@prod:~/shop.pl. Potwierdź, podając --confirm=shop@prod (podgląd: --dry-run).\n", $stderr);
        $this->assertSame([], $this->processCalls);

        [$exitCode] = $this->runCommand(DeployMode::Fresh, ['shop', '--production', '--confirm=shop@prod']);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString("export DEPLOY_FRESH=1\n", (string) $this->callsOf('bash')[0][1]);
        $this->assertStringContainsString("export DEPLOY_FRESH=1\n", (string) $this->lastProcessCall()[1]);
    }

    #[Test]
    public function dry_run_projektu_pokazuje_oba_srodowiska_i_nic_nie_uruchamia(): void
    {
        [$exitCode, $stdout] = $this->runCommand(DeployMode::Deploy, ['shop', '--dry-run']);

        $this->assertSame(0, $exitCode, $stdout);
        $this->assertStringContainsString("Przed-produkcja: {$this->preproduction}, PHP: php85;", $stdout);
        $this->assertStringContainsString('Produkcja: po potwierdzeniu pytania „Wdrożyć też na produkcję?” (bez terminala: nie).', $stdout);
        $this->assertStringContainsString('Serwer: shop@prod, katalog: ~/shop.pl, PHP: php84.', $stdout);
        $this->assertSame([], $this->lockCalls);
        $this->assertSame([], array_filter($this->processCalls, fn(array $call): bool => $call[0][0] !== 'git'));
        $this->assertFileDoesNotExist("{$this->preproduction}/composer.deploy.json");
    }

    #[Test]
    public function wdraza_manifest_scp_i_ssh(): void
    {
        [$exitCode, $stdout] = $this->runCommand(DeployMode::Deploy, []);

        $this->assertSame(0, $exitCode, $stdout);
        $this->assertSame([['--deploy', "--root={$this->root}"]], $this->lockCalls);
        $commands = array_map(fn(array $call): array => $call[0], $this->processCalls);
        $this->assertContains(['git', '-C', "{$this->root}/packages/acme/core", 'fetch', '--quiet', 'origin'], $commands);
        $this->assertContains(['scp', '-q', "{$this->root}/composer.deploy.json", "{$this->root}/composer.deploy.lock", 'user@host:~/app.pl/'], $commands);
        $ssh = $this->lastProcessCall();
        $this->assertSame(['ssh', 'user@host', 'bash -s'], $ssh[0]);
        $this->assertSame("export DEPLOY_PHP='php84' GITHUB_TOKEN='sekret'\ncd ~/'app.pl' || exit 1\necho serwer\n", $ssh[1]);
        $this->assertStringEndsWith("==> Gotowe\n", $stdout);
    }

    #[Test]
    public function nie_wysyla_lokalnego_env_production_na_serwer(): void
    {
        file_put_contents("{$this->root}/.env.production", "APP_ENV=production\n");

        [$exitCode, $stdout, $stderr] = $this->runCommand(DeployMode::Deploy, []);

        $this->assertSame(0, $exitCode, $stdout);
        $sentEnv = array_filter($this->processCalls, fn(array $call): bool => $call[0][0] === 'scp' && array_filter($call[0], fn(string $argument): bool => str_contains($argument, '.env')) !== []);
        $this->assertSame([], $sentEnv);
        $this->assertStringNotContainsString('.env', $stdout);
        $this->assertSame('', $stderr);
    }

    #[Test]
    public function dry_run_mowi_ze_env_production_jest_kopiowany_na_serwerze(): void
    {
        file_put_contents("{$this->root}/.env.production", "APP_ENV=production\n");
        [, $plan] = $this->runCommand(DeployMode::Deploy, ['--dry-run']);

        $this->assertStringContainsString('Środowisko: .env.production z serwera (~/app.pl) zostanie skopiowany jako .env (APP_KEY serwera zostaje, gdy plik go nie podaje; bez pliku .env bez zmian); lokalny plik nie jest wysyłany.', $plan);
        $this->assertSame([], array_filter($this->processCalls, fn(array $call): bool => $call[0][0] === 'scp'));
    }

    #[Test]
    public function onboarding_dopisuje_konta_do_skryptu(): void
    {
        [$exitCode] = $this->runCommand(DeployMode::Onboarding, []);

        $this->assertSame(0, $exitCode);
        $script = (string) $this->lastProcessCall()[1];
        $this->assertStringEndsWith("echo serwer\nstep 'Onboarding: konto a@example.com (admiro)'\n\"\$php_bin\" artisan 'admiro:account:create' 'a@example.com' 'Jan Kowalski' '--role=hero' '--skip-existing'\n", $script);
    }

    #[Test]
    public function dry_run_pokazuje_plan_bez_manifestu_scp_i_ssh(): void
    {
        [$exitCode, $stdout] = $this->runCommand(DeployMode::Onboarding, ['--dry-run']);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Serwer: user@host, katalog: ~/app.pl, PHP: php84.', $stdout);
        $this->assertStringContainsString('Konto: a@example.com (Jan Kowalski), strefa admiro, role: hero.', $stdout);
        $this->assertStringNotContainsString('sekret', $stdout);
        $this->assertSame([], $this->lockCalls);
        $this->assertSame([], array_filter($this->processCalls, fn(array $call): bool => $call[0][0] !== 'git'));
    }

    #[Test]
    public function fresh_bez_potwierdzenia_niczego_nie_uruchamia(): void
    {
        [$exitCode, , $stderr] = $this->runCommand(DeployMode::Fresh, []);

        $this->assertSame(1, $exitCode);
        $this->assertSame("deploy:fresh usunie wszystkie tabele i dane bazy na user@host:~/app.pl. Potwierdź, podając --confirm=user@host (podgląd: --dry-run).\n", $stderr);
        $this->assertSame([], $this->processCalls);
        $this->assertSame([], $this->lockCalls);
    }

    #[Test]
    public function fresh_z_potwierdzeniem_innego_serwera_niczego_nie_uruchamia(): void
    {
        [$exitCode, , $stderr] = $this->runCommand(DeployMode::Fresh, ['--confirm=user@other']);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('--confirm=user@host', $stderr);
        $this->assertSame([], $this->processCalls);
    }

    #[Test]
    public function fresh_z_potwierdzeniem_czysci_baze_i_zaklada_konta(): void
    {
        [$exitCode, $stdout] = $this->runCommand(DeployMode::Fresh, ['--confirm=user@host']);

        $this->assertSame(0, $exitCode, $stdout);
        $script = (string) $this->lastProcessCall()[1];
        $this->assertStringContainsString("cd ~/'app.pl' || exit 1\n", $script);
        $this->assertStringContainsString("export DEPLOY_FRESH=1\n", $script);
        $this->assertStringContainsString("\"\$php_bin\" artisan 'admiro:account:create' 'a@example.com' 'Jan Kowalski' '--role=hero' '--skip-existing'\n", $script);
    }

    #[Test]
    public function fresh_dry_run_ostrzega_bez_potwierdzenia(): void
    {
        [$exitCode, $stdout] = $this->runCommand(DeployMode::Fresh, ['--dry-run']);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Baza: migrate:fresh — wszystkie tabele i dane na serwerze zostaną usunięte (uruchomienie: --confirm=user@host).', $stdout);
        $this->assertStringContainsString('Konto: a@example.com (Jan Kowalski), strefa admiro, role: hero.', $stdout);
        $this->assertSame([], $this->lockCalls);
    }

    #[Test]
    public function zwykle_wdrozenie_nie_czysci_bazy_ani_nie_przyjmuje_potwierdzenia(): void
    {
        [$exitCode] = $this->runCommand(DeployMode::Onboarding, []);
        [$unknown, , $stderr] = $this->runCommand(DeployMode::Deploy, ['--confirm=user@host']);

        $this->assertSame(0, $exitCode);
        $this->assertStringNotContainsString('DEPLOY_FRESH', (string) $this->lastProcessCall()[1]);
        $this->assertSame(1, $unknown);
        $this->assertStringStartsWith("Nieznana opcja: --confirm=user@host\n", $stderr);
    }

    #[Test]
    public function niewypchniete_commity_wstrzymuja_wdrozenie(): void
    {
        $this->unpushed = ["{$this->root}/packages/acme/core"];

        [$exitCode, , $stderr] = $this->runCommand(DeployMode::Deploy, []);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Niewypchnięte commity: packages/acme/core. Zrób git push', $stderr);
        $this->assertSame([], $this->lockCalls);
    }

    #[Test]
    public function with_push_wypycha_najpierw_paczki_potem_projekt_i_wdraza(): void
    {
        $this->unpushed = ["{$this->root}/packages/acme/core", $this->root];

        [$exitCode, $stdout] = $this->runCommand(DeployMode::Deploy, ['--with-push']);

        $this->assertSame(0, $exitCode, $stdout);
        $pushes = array_values(array_filter(array_map(fn(array $call): array => $call[0], $this->processCalls), fn(array $command): bool => ($command[3] ?? null) === 'push'));
        $this->assertSame([['git', '-C', "{$this->root}/packages/acme/core", 'push', '--quiet'], ['git', '-C', $this->root, 'push', '--quiet']], $pushes);
        $this->assertStringContainsString("Wypchnięte: packages/acme/core, ..\n", $stdout);
        $this->assertSame([['--deploy', "--root={$this->root}"]], $this->lockCalls);
        $this->assertSame(['ssh', 'user@host', 'bash -s'], $this->lastProcessCall()[0]);
    }

    #[Test]
    public function with_push_z_dry_run_tylko_pokazuje_co_wypchnie(): void
    {
        $this->unpushed = ["{$this->root}/packages/acme/core"];

        [$exitCode, $stdout] = $this->runCommand(DeployMode::Deploy, ['--with-push', '--dry-run']);

        $this->assertSame(0, $exitCode, $stdout);
        $this->assertStringContainsString("Do wypchnięcia (--with-push): packages/acme/core.\n", $stdout);
        $this->assertNotContains('push', array_merge(...array_map(fn(array $call): array => $call[0], $this->processCalls)));
        $this->assertSame([], $this->lockCalls);
    }

    #[Test]
    public function nieudany_push_wstrzymuje_wdrozenie(): void
    {
        $this->unpushed = ["{$this->root}/packages/acme/core"];
        $this->pushExitCode = 128;

        [$exitCode, , $stderr] = $this->runCommand(DeployMode::Deploy, ['--with-push']);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Nie udało się wypchnąć packages/acme/core (git push).', $stderr);
        $this->assertSame([], $this->lockCalls);
    }

    #[Test]
    public function brak_konfiguracji_to_czytelny_blad(): void
    {
        unlink("{$this->root}/infrastructure/deploy.env");
        exec('rm -rf ' . escapeshellarg("{$this->root}/infrastructure/deploy-vendors"));

        [$exitCode, , $stderr] = $this->runCommand(DeployMode::Deploy, []);

        $this->assertSame(1, $exitCode);
        $this->assertSame("Brak infrastructure/deploy.env — skopiuj infrastructure/deploy.env.example i uzupełnij.\n", $stderr);
        $this->assertSame([], $this->processCalls);
    }

    #[Test]
    public function opcja_onboardingu_tylko_w_onboardingu(): void
    {
        [$exitCode, , $stderr] = $this->runCommand(DeployMode::Deploy, ['--onboarding=x.env']);

        $this->assertSame(1, $exitCode);
        $this->assertStringStartsWith("Nieznana opcja: --onboarding=x.env\n", $stderr);
    }

    /**
     * Wywołania danego programu (np. `bash`, `scp`, `ssh`) w kolejności.
     *
     * @return list<array{0: list<string>, 1: string|null, 2: string|null}>
     */
    private function callsOf(string $program): array
    {
        return array_values(array_filter($this->processCalls, fn(array $call): bool => $call[0][0] === $program));
    }

    /**
     * @return array{0: list<string>, 1: string|null, 2: string|null}
     */
    private function lastProcessCall(): array
    {
        $call = end($this->processCalls);
        $this->assertIsArray($call);

        return $call;
    }

    /**
     * @param  list<string>  $arguments
     * @return array{0: int, 1: string, 2: string}
     */
    private function runCommand(DeployMode $mode, array $arguments): array
    {
        $processes = new class ($this->pushExitCode, $this->preproductionExitCode) implements ProcessRunner {
            /**
             * @var list<array{0: list<string>, 1: string|null, 2: string|null}>
             */
            public array $calls = [];

            /**
             * @var list<string>
             */
            public array $pushed = [];

            public function __construct(private int $pushExitCode, private int $preproductionExitCode) {}

            public function run(array $command, ?string $input = null, ?string $directory = null): int
            {
                $this->calls[] = [$command, $input, $directory];

                if ($command[0] === 'bash') {
                    return $this->preproductionExitCode;
                }

                if (($command[3] ?? null) !== 'push') {
                    return 0;
                }

                if ($this->pushExitCode === 0) {
                    $this->pushed[] = $command[2];
                }

                return $this->pushExitCode;
            }

            public function capture(array $command, ?string $directory = null): string
            {
                $this->calls[] = [$command, null, $directory];

                return '';
            }
        };
        $unpushed = $this->unpushed;
        $isPushed = fn(string $directory): bool => in_array($directory, $processes->pushed, true);
        $repositories = new class ($unpushed, $isPushed) implements RepositorySource {
            /**
             * @param  list<string>  $unpushed
             * @param  Closure(string): bool  $isPushed
             */
            public function __construct(private array $unpushed, private Closure $isPushed) {}

            public function originUrl(string $directory): ?string
            {
                return null;
            }

            public function hasUnpushedCommits(string $directory): bool
            {
                return in_array($directory, $this->unpushed, true) && ! ($this->isPushed)($directory);
            }
        };
        $lock = new class ($this->root) implements Command {
            /**
             * @var list<list<string>>
             */
            public array $calls = [];

            public function __construct(private string $root) {}

            public function name(): string
            {
                return 'deploy:lock';
            }

            public function description(): string
            {
                return '';
            }

            public function run(array $arguments, string $workingDirectory, $stdout, $stderr): int
            {
                $this->calls[] = $arguments;
                file_put_contents("{$this->root}/composer.deploy.json", '{}');
                file_put_contents("{$this->root}/composer.deploy.lock", '{}');

                return 0;
            }
        };
        $stdout = fopen('php://memory', 'w+');
        $stderr = fopen('php://memory', 'w+');
        $this->assertIsResource($stdout);
        $this->assertIsResource($stderr);

        $confirm = function (string $question): bool {
            $this->questions[] = $question;

            return $this->answer;
        };
        $command = new DeployCommand($mode, $processes, new RepositoryCheck($processes, $repositories), new RemoteScript("echo serwer\n"), fn(): Command => $lock, $confirm);
        $exitCode = $command->run($arguments, $this->root, $stdout, $stderr);
        $this->processCalls = [...$this->processCalls, ...$processes->calls];
        $this->lockCalls = [...$this->lockCalls, ...$lock->calls];

        return [$exitCode, (string) stream_get_contents($stdout, -1, 0), (string) stream_get_contents($stderr, -1, 0)];
    }
}
