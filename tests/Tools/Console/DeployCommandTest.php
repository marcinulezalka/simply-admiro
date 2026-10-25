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
 * Wdrożenie w katalogu tymczasowym: git, scp i ssh zastąpione zapisem wywołań, manifest — atrapą polecenia.
 */
final class DeployCommandTest extends TestCase
{
    private string $root;

    /**
     * @var list<array{0: list<string>, 1: string|null}>
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

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/deploy-' . bin2hex(random_bytes(4));
        mkdir("{$this->root}/infrastructure", recursive: true);
        mkdir("{$this->root}/.git");
        mkdir("{$this->root}/packages/acme/core/.git", recursive: true);
        file_put_contents("{$this->root}/infrastructure/deploy.env", "DEPLOY_SSH=user@host\nDEPLOY_PATH=~/app.pl\nGITHUB_TOKEN='sekret'\n");
        file_put_contents("{$this->root}/infrastructure/deploy.onboarding.env", "ONBOARD_ACCOUNT_1_ZONE=admiro\nONBOARD_ACCOUNT_1_EMAIL=a@example.com\nONBOARD_ACCOUNT_1_NAME=\"Jan Kowalski\"\nONBOARD_ACCOUNT_1_ROLES=hero\n");
    }

    protected function tearDown(): void
    {
        exec('rm -rf ' . escapeshellarg($this->root));
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
    public function brak_konfiguracji_to_czytelny_blad(): void
    {
        unlink("{$this->root}/infrastructure/deploy.env");

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
     * @return array{0: list<string>, 1: string|null}
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
        $processes = new class implements ProcessRunner {
            /**
             * @var list<array{0: list<string>, 1: string|null}>
             */
            public array $calls = [];

            public function run(array $command, ?string $input = null, ?string $directory = null): int
            {
                $this->calls[] = [$command, $input];

                return 0;
            }

            public function capture(array $command, ?string $directory = null): string
            {
                $this->calls[] = [$command, null];

                return '';
            }
        };
        $unpushed = $this->unpushed;
        $repositories = new class ($unpushed) implements RepositorySource {
            /**
             * @param  list<string>  $unpushed
             */
            public function __construct(private array $unpushed) {}

            public function originUrl(string $directory): ?string
            {
                return null;
            }

            public function hasUnpushedCommits(string $directory): bool
            {
                return in_array($directory, $this->unpushed, true);
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

        $command = new DeployCommand($mode, $processes, new RepositoryCheck($processes, $repositories), new RemoteScript("echo serwer\n"), fn(): Command => $lock);
        $exitCode = $command->run($arguments, $this->root, $stdout, $stderr);
        $this->processCalls = [...$this->processCalls, ...$processes->calls];
        $this->lockCalls = [...$this->lockCalls, ...$lock->calls];

        return [$exitCode, (string) stream_get_contents($stdout, -1, 0), (string) stream_get_contents($stderr, -1, 0)];
    }
}
