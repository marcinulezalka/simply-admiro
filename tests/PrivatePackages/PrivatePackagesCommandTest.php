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

namespace Simplysmart\Tools\Tests\PrivatePackages;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Simplysmart\Tools\Console\PrivatePackagesCommand;
use Simplysmart\Tools\PrivatePackages\ComposerRunner;

/**
 * Instalacja paczek prywatnych — zawsze w katalogu tymczasowym, z composerem zastąpionym zapisem wywołań.
 */
final class PrivatePackagesCommandTest extends TestCase
{
    private string $root;

    /**
     * @var list<array{0: string, 1: string, 2: list<string>, 3?: list<string>}>
     */
    private array $composerCalls = [];

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/composer-private-' . bin2hex(random_bytes(4));
        mkdir($this->root);
        file_put_contents("{$this->root}/composer.json", <<<'JSON'
            {
                "name": "acme/app",
                "require": {
                    "php": "^8.4"
                },
                "extra": {
                    "laravel": {
                        "dont-discover": []
                    },
                    "empty": {}
                }
            }
            JSON);
        file_put_contents("{$this->root}/composer.lock", '{"packages": []}');
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob("{$this->root}/*") ?: []);
        rmdir($this->root);
    }

    #[Test]
    public function bez_listy_nic_nie_instaluje(): void
    {
        [$exitCode, $stdout] = $this->runCommand([]);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('nic do zainstalowania', $stdout);
        $this->assertFileDoesNotExist("{$this->root}/composer.private.json");
        $this->assertSame([], $this->composerCalls);
    }

    #[Test]
    public function scala_paczki_prywatne_i_uruchamia_composera(): void
    {
        $this->writeList('{"require": {"acme/secret": "@dev"}}');

        [$exitCode, $stdout] = $this->runCommand([]);

        $this->assertSame(0, $exitCode);
        $this->assertSame("Paczki prywatne: acme/secret.\n", $stdout);
        $manifest = (string) file_get_contents("{$this->root}/composer.private.json");
        $this->assertStringContainsString('"acme/secret": "@dev"', $manifest);
        $this->assertStringContainsString('"empty": {}', $manifest);
        $this->assertStringContainsString('"dont-discover": []', $manifest);
        $this->assertStringNotContainsString('acme/secret', (string) file_get_contents("{$this->root}/composer.json"));
        $this->assertFileEquals("{$this->root}/composer.lock", "{$this->root}/composer.private.lock");
        $this->assertSame([[$this->root, 'composer.private.json', ['acme/secret']]], $this->composerCalls);
    }

    #[Test]
    public function istniejacego_locka_prywatnego_nie_nadpisuje(): void
    {
        $this->writeList('{"require": {"acme/secret": "@dev"}}');
        file_put_contents("{$this->root}/composer.private.lock", '{"private": true}');

        $this->runCommand([]);

        $this->assertSame('{"private": true}', file_get_contents("{$this->root}/composer.private.lock"));
    }

    #[Test]
    public function dry_run_nie_zapisuje_i_nie_uruchamia_composera(): void
    {
        $this->writeList('{"require": {"acme/secret": "@dev"}}');

        [$exitCode, $stdout] = $this->runCommand(['--dry-run']);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Tryb --dry-run', $stdout);
        $this->assertFileDoesNotExist("{$this->root}/composer.private.json");
        $this->assertSame([], $this->composerCalls);
    }

    #[Test]
    public function bledna_lista_konczy_sie_bledem(): void
    {
        foreach (['{"require": ', '{"require": {"Bad Name": "@dev"}}', '{"require": {"acme/secret": 1}}', '{"packages": []}'] as $list) {
            $this->writeList($list);

            [$exitCode, , $stderr] = $this->runCommand([]);

            $this->assertSame(1, $exitCode, $list);
            $this->assertNotSame('', $stderr, $list);
        }

        $this->assertSame([], $this->composerCalls);
    }

    #[Test]
    public function kod_composera_jest_kodem_wyjscia(): void
    {
        $this->writeList('{"require": {"acme/secret": "@dev"}}');

        [$exitCode] = $this->runCommand([], 2);

        $this->assertSame(2, $exitCode);
    }

    #[Test]
    public function nieznana_opcja_pokazuje_uzycie(): void
    {
        [$exitCode, , $stderr] = $this->runCommand(['--force']);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Użycie: composer-private', $stderr);
    }

    private function writeList(string $contents): void
    {
        file_put_contents("{$this->root}/private-packages.json", $contents);
    }

    /**
     * @param  list<string>  $arguments
     * @return array{int, string, string}
     */
    private function runCommand(array $arguments, int $composerExitCode = 0): array
    {
        $composer = new class ($composerExitCode) implements ComposerRunner {
            /**
             * @var list<array{0: string, 1: string, 2: list<string>, 3?: list<string>}>
             */
            public array $calls = [];

            public function __construct(private int $exitCode) {}

            public function update(string $root, string $manifest, array $packages, array $options = []): int
            {
                $this->calls[] = $options === [] ? [$root, $manifest, $packages] : [$root, $manifest, $packages, $options];

                return $this->exitCode;
            }
        };
        $stdout = fopen('php://memory', 'w+');
        $stderr = fopen('php://memory', 'w+');
        $this->assertIsResource($stdout);
        $this->assertIsResource($stderr);

        $exitCode = (new PrivatePackagesCommand($composer))->run($arguments, $this->root, $stdout, $stderr);
        $this->composerCalls = [...$this->composerCalls, ...$composer->calls];

        return [$exitCode, (string) stream_get_contents($stdout, -1, 0), (string) stream_get_contents($stderr, -1, 0)];
    }
}
