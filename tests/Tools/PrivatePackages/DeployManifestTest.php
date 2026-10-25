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

namespace Simplysmart\Admiro\Tests\Tools\PrivatePackages;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Simplysmart\Admiro\Tools\Console\PrivatePackagesCommand;
use Simplysmart\Admiro\Tools\PrivatePackages\ComposerRunner;
use Simplysmart\Admiro\Tools\PrivatePackages\RepositorySource;

/**
 * Tryb --deploy w katalogu tymczasowym: manifest bez repozytoriów path, z repozytoriami git wymaganych paczek
 * (także require-dev i prywatnych), lock z composer.private.lock, composer z --no-install, ostrzeżenie
 * o niewypchniętych commitach, błąd bez origin, dry-run bez zapisu i projekt bez repozytoriów path.
 */
final class DeployManifestTest extends TestCase
{
    private string $root;

    /**
     * @var list<array{0: string, 1: string, 2: list<string>, 3: list<string>}>
     */
    private array $composerCalls = [];

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/composer-deploy-' . bin2hex(random_bytes(4));
        mkdir($this->root);
        file_put_contents("{$this->root}/composer.json", <<<'JSON'
            {
                "name": "acme/app",
                "require": {"php": "^8.4", "acme/core": "@dev"},
                "require-dev": {"acme/tools": "@dev"},
                "repositories": [
                    {"name": "local", "type": "path", "url": "packages/*/*", "options": {"symlink": true}},
                    {"type": "composer", "url": "https://repo.example.com"}
                ]
            }
            JSON);
        file_put_contents("{$this->root}/composer.lock", '{"public": true}');

        foreach (['core', 'tools', 'secret', 'unused'] as $package) {
            mkdir("{$this->root}/packages/acme/{$package}", 0777, true);
            file_put_contents("{$this->root}/packages/acme/{$package}/composer.json", json_encode(['name' => "acme/{$package}"]));
        }
    }

    protected function tearDown(): void
    {
        exec('rm -rf ' . escapeshellarg($this->root));
    }

    #[Test]
    public function zapisuje_manifest_z_repozytoriami_git_i_lock_bez_instalacji(): void
    {
        file_put_contents("{$this->root}/private-packages.json", '{"require": {"acme/secret": "@dev"}}');
        file_put_contents("{$this->root}/composer.private.lock", '{"private": true}');

        [$exitCode, $stdout, $stderr] = $this->runCommand(['--deploy'], unpushed: ['acme/core']);

        $this->assertSame(0, $exitCode);
        $this->assertSame("Paczki z repozytoriów git: acme/core, acme/secret, acme/tools.\n", $stdout);
        $this->assertStringContainsString('acme/core ma niewypchnięte commity', $stderr);
        $manifest = json_decode((string) file_get_contents("{$this->root}/composer.deploy.json"), true);
        $this->assertIsArray($manifest);
        $this->assertIsArray($manifest['require']);
        $this->assertSame('@dev', $manifest['require']['acme/secret']);
        $this->assertSame([
            ['type' => 'composer', 'url' => 'https://repo.example.com'],
            ['type' => 'vcs', 'url' => 'https://github.com/acme/core.git'],
            ['type' => 'vcs', 'url' => 'https://github.com/acme/secret.git'],
            ['type' => 'vcs', 'url' => 'https://github.com/acme/tools.git'],
        ], $manifest['repositories']);
        $this->assertSame('{"private": true}', file_get_contents("{$this->root}/composer.deploy.lock"));
        $this->assertStringNotContainsString('acme/secret', (string) file_get_contents("{$this->root}/composer.json"));
        $this->assertSame([[$this->root, 'composer.deploy.json', ['acme/core', 'acme/secret', 'acme/tools'], ['--no-install', '--no-scripts']]], $this->composerCalls);
    }

    #[Test]
    public function bez_listy_prywatnej_bierze_lock_publiczny(): void
    {
        [$exitCode] = $this->runCommand(['--deploy']);

        $this->assertSame(0, $exitCode);
        $this->assertSame('{"public": true}', file_get_contents("{$this->root}/composer.deploy.lock"));
        $this->assertSame(['acme/core', 'acme/tools'], $this->composerCalls[0][2]);
    }

    #[Test]
    public function paczka_bez_origin_to_blad(): void
    {
        [$exitCode, , $stderr] = $this->runCommand(['--deploy'], missing: ['acme/tools']);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Paczka acme/tools', $stderr);
        $this->assertFileDoesNotExist("{$this->root}/composer.deploy.json");
        $this->assertSame([], $this->composerCalls);
    }

    #[Test]
    public function dry_run_nie_zapisuje_i_nie_uruchamia_composera(): void
    {
        [$exitCode, $stdout] = $this->runCommand(['--deploy', '--dry-run']);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Tryb --dry-run', $stdout);
        $this->assertFileDoesNotExist("{$this->root}/composer.deploy.json");
        $this->assertSame([], $this->composerCalls);
    }

    #[Test]
    public function bez_repozytoriow_path_manifest_jest_niepotrzebny(): void
    {
        file_put_contents("{$this->root}/composer.json", '{"name": "acme/app", "require": {"acme/core": "^1.0"}}');

        [$exitCode, $stdout] = $this->runCommand(['--deploy']);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('niepotrzebny', $stdout);
        $this->assertSame([], $this->composerCalls);
    }

    /**
     * @param  list<string>  $arguments
     * @param  list<string>  $unpushed  Paczki z niewypchniętymi commitami.
     * @param  list<string>  $missing  Paczki bez origin.
     * @return array{int, string, string}
     */
    private function runCommand(array $arguments, array $unpushed = [], array $missing = []): array
    {
        $composer = new class implements ComposerRunner {
            /**
             * @var list<array{0: string, 1: string, 2: list<string>, 3: list<string>}>
             */
            public array $calls = [];

            public function update(string $root, string $manifest, array $packages, array $options = []): int
            {
                $this->calls[] = [$root, $manifest, $packages, $options];

                return 0;
            }
        };
        $repositories = new class ($unpushed, $missing) implements RepositorySource {
            /**
             * @param  list<string>  $unpushed
             * @param  list<string>  $missing
             */
            public function __construct(private array $unpushed, private array $missing) {}

            public function originUrl(string $directory): ?string
            {
                $name = 'acme/' . basename($directory);

                return in_array($name, $this->missing, true) ? null : 'https://github.com/' . $name . '.git';
            }

            public function hasUnpushedCommits(string $directory): bool
            {
                return in_array('acme/' . basename($directory), $this->unpushed, true);
            }
        };
        $stdout = fopen('php://memory', 'w+');
        $stderr = fopen('php://memory', 'w+');
        $this->assertIsResource($stdout);
        $this->assertIsResource($stderr);

        $exitCode = (new PrivatePackagesCommand($composer, $repositories))->run($arguments, $this->root, $stdout, $stderr);
        $this->composerCalls = [...$this->composerCalls, ...$composer->calls];

        return [$exitCode, (string) stream_get_contents($stdout, -1, 0), (string) stream_get_contents($stderr, -1, 0)];
    }
}
