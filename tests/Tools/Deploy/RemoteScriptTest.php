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

namespace Simplysmart\Admiro\Tests\Tools\Deploy;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Simplysmart\Admiro\Tools\Deploy\DeployConfig;
use Simplysmart\Admiro\Tools\Deploy\OnboardingAccount;
use Simplysmart\Admiro\Tools\Deploy\RemoteScript;
use Simplysmart\Admiro\Tools\Deploy\ShellArgument;

/**
 * Skrypt dla `ssh … 'bash -s'`: wartości z konfiguracji docierają do basha bez zmian i bez wykonania.
 * Skrypt serwerowy jest zastąpiony echem zmiennych, a artisan — funkcją wypisującą argumenty.
 */
final class RemoteScriptTest extends TestCase
{
    /**
     * @return iterable<string, array{0: string}>
     */
    public static function dangerousValues(): iterable
    {
        yield 'spacje' => ['Jan Kowalski'];
        yield 'apostrof' => ["O'Brien"];
        yield 'cudzysłów' => ['Anna "Ania" Nowak'];
        yield 'podstawienie' => ['$(touch /tmp/simplysmart-pwned) `id` $HOME'];
        yield 'średnik i backslash' => ['a; exit 7 \\ b \\\''];
        yield 'polskie znaki' => ['Zażółć Gęślą Jaźń'];
        yield 'nowa linia' => ["dwie\nlinie"];
    }

    #[Test]
    #[DataProvider('dangerousValues')]
    public function wartosci_docieraja_do_basha_bez_zmian(string $value): void
    {
        $directory = sys_get_temp_dir() . '/remote-script-' . bin2hex(random_bytes(4));
        mkdir($directory);
        $config = new DeployConfig(ssh: 'host', path: $directory, php: $value, remoteComposer: $value, githubToken: $value);
        $account = new OnboardingAccount('admiro', $value, $value, [$value], $value);
        $server = <<<'BASH'
            step() { :; }
            php_bin=php_stub
            php_stub() { shift; printf '%s\0' "$@"; }
            printf '%s\0' "$DEPLOY_PHP" "$GITHUB_TOKEN" "$COMPOSER_BIN" "$PWD"
            BASH;
        $script = (new RemoteScript($server))->build($config, [$account]);

        $output = $this->bash($script);
        rmdir($directory);

        $this->assertSame(
            [$value, $value, $value, $directory, 'admiro:account:create', $value, $value, "--role={$value}", "--organization={$value}", '--skip-existing'],
            $output,
        );
        $this->assertFileDoesNotExist('/tmp/simplysmart-pwned');
    }

    #[Test]
    public function zachowuje_katalog_domowy_w_sciezce(): void
    {
        $this->assertSame("~/'moja domena.pl'", ShellArgument::path('~/moja domena.pl'));
        $this->assertSame('~', ShellArgument::path('~'));
        $this->assertSame("'/srv/~app'", ShellArgument::path('/srv/~app'));
    }

    #[Test]
    public function skrypt_serwerowy_paczki_ma_poprawna_skladnie(): void
    {
        $script = RemoteScript::fromPackage()->build(new DeployConfig('host', '~/app'), [new OnboardingAccount('admiro', 'a@example.com', 'Jan')]);

        $process = proc_open(['bash', '-n'], [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes);
        $this->assertIsResource($process);
        fwrite($pipes[0], $script);
        fclose($pipes[0]);
        $errors = (string) stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $this->assertSame(0, proc_close($process), $errors);
        $this->assertStringNotContainsString('COMPOSER_BIN', strtok($script, "\n") ?: '');
        $this->assertStringContainsString("\"\$php_bin\" artisan 'admiro:account:create' 'a@example.com' 'Jan' '--skip-existing'\n", $script);
    }

    #[Test]
    public function skrypt_serwerowy_przy_fresh_zdejmuje_blokade_tylko_dla_migrate_fresh(): void
    {
        $script = RemoteScript::fromPackage()->build(new DeployConfig('host', '~/app'), freshDatabase: true);
        $fresh = strpos($script, 'MAINBOARD_ALLOW_DESTRUCTIVE_COMMANDS=true "$php_bin" artisan migrate:fresh --force');
        $configClear = strpos($script, '"$php_bin" artisan config:clear');
        $seed = strpos($script, 'artisan mainboard:localization:seed');

        $this->assertStringStartsWith("export DEPLOY_PHP='php84' GITHUB_TOKEN=''\nexport DEPLOY_FRESH=1\n", $script);
        $this->assertStringContainsString('if [[ "${DEPLOY_FRESH:-}" == 1 ]]; then', $script);
        $this->assertIsInt($fresh);
        $this->assertIsInt($configClear);
        $this->assertIsInt($seed);
        $this->assertLessThan($fresh, $configClear);
        $this->assertLessThan($seed, $fresh);
        $this->assertSame(1, substr_count($script, 'MAINBOARD_ALLOW_DESTRUCTIVE_COMMANDS'));
    }

    #[Test]
    public function skrypt_serwerowy_bez_fresh_nie_eksportuje_flagi(): void
    {
        $this->assertStringNotContainsString('export DEPLOY_FRESH', RemoteScript::fromPackage()->build(new DeployConfig('host', '~/app')));
    }

    #[Test]
    public function skrypt_serwerowy_zaklada_organizacje_operatora_przed_rolami(): void
    {
        $script = RemoteScript::fromPackage()->build(new DeployConfig('host', '~/app'));
        $organizations = strpos($script, 'artisan mainboard:organizations:seed');
        $permissions = strpos($script, 'artisan mainboard:permissions:sync');

        $this->assertIsInt($organizations);
        $this->assertIsInt($permissions);
        $this->assertLessThan($permissions, $organizations);
    }

    #[Test]
    public function skrypt_serwerowy_tworzy_skrypt_crona_po_cache_z_php_wdrozenia(): void
    {
        $script = RemoteScript::fromPackage()->build(new DeployConfig('host', '~/app'));
        $cache = strpos($script, 'artisan view:cache');
        $cron = strpos($script, '"$php_bin" artisan mainboard:cron:script --php="$php_bin"');

        $this->assertIsInt($cache);
        $this->assertIsInt($cron);
        $this->assertLessThan($cron, $cache);
        $this->assertStringContainsString("grep -qx 'mainboard:cron:script'", $script);
    }

    /**
     * @return list<string> Wartości wypisane przez skrypt (rozdzielone bajtem zerowym).
     */
    private function bash(string $script): array
    {
        $process = proc_open(['bash', '-s'], [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes);
        $this->assertIsResource($process);
        fwrite($pipes[0], $script);
        fclose($pipes[0]);
        $output = (string) stream_get_contents($pipes[1]);
        $errors = (string) stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $this->assertSame(0, proc_close($process), $errors);

        return explode("\0", rtrim($output, "\0"));
    }
}
