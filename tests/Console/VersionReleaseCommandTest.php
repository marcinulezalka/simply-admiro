<?php

declare(strict_types=1);

namespace Simplysmart\Tools\Tests\Console;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Simplysmart\Tools\Console\VersionReleaseCommand;
use Simplysmart\Tools\Release\ReleaseClock;
use Simplysmart\Tools\Release\ReleasePlanner;
use Simplysmart\Tools\Release\VersionRelease;

/**
 * Wydanie przez polecenie i punkt wejścia bin/version-release — zawsze w katalogu tymczasowym.
 */
final class VersionReleaseCommandTest extends TestCase
{
    private const string BIN = __DIR__ . '/../../bin/version-release';

    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/version-release-' . bin2hex(random_bytes(4));
        mkdir($this->root);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob("{$this->root}/*") ?: []);
        rmdir($this->root);
    }

    #[Test]
    public function bez_root_wydaje_katalog_roboczy(): void
    {
        $this->writeProject('1.2.3', "### Added\n\n- Funkcja.\n");

        [$exitCode, $stdout] = $this->runCommand([], $this->root);

        $this->assertSame(0, $exitCode);
        $this->assertSame("Wersja 1.2.3 → 1.3.0 (minor).\n", $stdout);
        $this->assertSame("1.3.0\n", file_get_contents("{$this->root}/VERSION"));
    }

    #[Test]
    public function root_wskazuje_cel_wydania(): void
    {
        $this->writeProject('0.1.0', "### Fixed\n\n- Poprawka.\n");

        [$exitCode] = $this->runCommand(["--root={$this->root}"], sys_get_temp_dir());

        $this->assertSame(0, $exitCode);
        $this->assertSame("0.1.1\n", file_get_contents("{$this->root}/VERSION"));
    }

    #[Test]
    public function brak_wpisow_nie_zmienia_plikow(): void
    {
        $this->writeProject('0.1.0', "### Added\n");

        [$exitCode, $stdout] = $this->runCommand([], $this->root);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('nic do wydania', $stdout);
        $this->assertSame("0.1.0\n", file_get_contents("{$this->root}/VERSION"));
    }

    #[Test]
    public function dry_run_pokazuje_plan_bez_zmiany_plikow(): void
    {
        $this->writeProject('1.2.3', "### Added\n\n- Funkcja.\n");
        $changelog = file_get_contents("{$this->root}/CHANGELOG.md");

        [$exitCode, $stdout] = $this->runCommand(['--dry-run'], $this->root);

        $this->assertSame(0, $exitCode);
        $this->assertSame("Wersja 1.2.3 → 1.3.0 (minor). Tryb --dry-run: pliki nie zostały zmienione.\n", $stdout);
        $this->assertSame("1.2.3\n", file_get_contents("{$this->root}/VERSION"));
        $this->assertSame($changelog, file_get_contents("{$this->root}/CHANGELOG.md"));
    }

    #[Test]
    public function nieznana_opcja_i_brak_katalogu_koncza_sie_bledem(): void
    {
        [$unknownCode, , $unknownError] = $this->runCommand(['--force'], $this->root);
        [$missingCode, , $missingError] = $this->runCommand(["--root={$this->root}/brak"], $this->root);
        [$emptyCode] = $this->runCommand(['--root='], $this->root);

        $this->assertSame([1, 1, 1], [$unknownCode, $missingCode, $emptyCode]);
        $this->assertStringContainsString('Nieznana opcja: --force', $unknownError);
        $this->assertStringContainsString('Katalog celu wydania nie istnieje', $missingError);
    }

    #[Test]
    public function punkt_wejscia_z_linii_polecen_wydaje_biezacy_katalog(): void
    {
        $this->writeProject('2.0.0', "### Fixed\n\n- Poprawka.\n");

        exec(sprintf('cd %s && %s %s 2>&1', escapeshellarg($this->root), PHP_BINARY, escapeshellarg((string) realpath(self::BIN))), $output, $exitCode);

        $this->assertSame(0, $exitCode, implode("\n", $output));
        $this->assertSame("2.0.1\n", file_get_contents("{$this->root}/VERSION"));
    }

    /**
     * @param  list<string>  $arguments
     * @return array{int, string, string} Kod wyjścia, STDOUT i STDERR.
     */
    private function runCommand(array $arguments, string $workingDirectory): array
    {
        $stdout = $this->memoryStream();
        $stderr = $this->memoryStream();
        $command = new VersionReleaseCommand(new VersionRelease(new ReleasePlanner(), new ReleaseClock()));

        $exitCode = $command->run($arguments, $workingDirectory, $stdout, $stderr);

        return [$exitCode, (string) stream_get_contents($stdout, -1, 0), (string) stream_get_contents($stderr, -1, 0)];
    }

    /**
     * @return resource
     */
    private function memoryStream()
    {
        $stream = fopen('php://memory', 'w+');
        $this->assertNotFalse($stream);

        return $stream;
    }

    private function writeProject(string $version, string $unreleased): void
    {
        file_put_contents("{$this->root}/VERSION", "{$version}\n");
        file_put_contents("{$this->root}/CHANGELOG.md", "# Changelog\n\n## [Unreleased]\n\n{$unreleased}\n## [{$version}] - 2026-01-01\n");
    }
}
