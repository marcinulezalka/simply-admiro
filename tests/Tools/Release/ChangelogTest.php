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

namespace Simplysmart\Admiro\Tests\Tools\Release;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Simplysmart\Admiro\Tools\Release\Changelog;
use Simplysmart\Admiro\Tools\Release\ReleaseBump;
use Simplysmart\Admiro\Tools\Release\ReleaseException;
use Simplysmart\Admiro\Tools\Release\SemanticVersion;

final class ChangelogTest extends TestCase
{
    /**
     * @return array<string, array{string, ReleaseBump|null}>
     */
    public static function unreleasedEntries(): array
    {
        return [
            'brak wpisów' => ["### Added\n\n### Fixed\n", null],
            'Fixed' => ["### Fixed\n\n- Poprawka.\n", ReleaseBump::Patch],
            'Added wygrywa z Fixed' => ["### Fixed\n\n- Poprawka.\n\n### Added\n\n- Funkcja.\n", ReleaseBump::Minor],
            'Deprecated' => ["### Deprecated\n\n- Stare API.\n", ReleaseBump::Minor],
            'Removed' => ["### Removed\n\n- Usunięto X.\n", ReleaseBump::Major],
            'BREAKING w Changed' => ["### Changed\n\n- **BREAKING** Nowy format.\n", ReleaseBump::Major],
            'słowo breaking w treści' => ["### Fixed\n\n- Poprawka line-breaking.\n", ReleaseBump::Patch],
        ];
    }

    #[Test]
    #[DataProvider('unreleasedEntries')]
    public function ustala_wymagane_podbicie(string $unreleased, ?ReleaseBump $expected): void
    {
        $this->assertSame($expected, $this->changelog($unreleased)->requiredBump());
    }

    #[Test]
    public function odrzuca_nieznana_sekcje(): void
    {
        $this->expectException(ReleaseException::class);
        $this->expectExceptionMessage('Nieznana sekcja „### Dodano”');

        $this->changelog("### Dodano\n\n- Funkcja.\n")->requiredBump();
    }

    #[Test]
    public function odrzuca_liczbowa_sekcje_bledem_wydania(): void
    {
        $this->expectException(ReleaseException::class);
        $this->expectExceptionMessage('Nieznana sekcja „### 2026”');

        $this->changelog("### 2026\n\n- Wpis.\n")->requiredBump();
    }

    #[Test]
    public function odrzuca_wpisy_bez_naglowka(): void
    {
        $this->expectException(ReleaseException::class);

        $this->changelog("- Wpis bez sekcji.\n")->requiredBump();
    }

    #[Test]
    public function zglasza_brak_sekcji_unreleased(): void
    {
        $this->expectException(ReleaseException::class);
        $this->expectExceptionMessage('brakuje sekcji');

        (new Changelog("# Changelog\n\n## [1.0.0] - 2026-01-01\n"))->unreleased();
    }

    #[Test]
    public function przenosi_wpisy_do_datowanej_sekcji_z_zachowaniem_crlf(): void
    {
        $changelog = new Changelog("# Changelog\r\n\r\n## [Unreleased]\r\n\r\n### Added\r\n\r\n- Funkcja.\r\n");

        $promoted = $changelog->promote(new SemanticVersion(1, 1, 0), new DateTimeImmutable('2026-10-19'));

        $this->assertStringContainsString("## [Unreleased]\r\n\r\n## [1.1.0] - 2026-10-19\r\n", $promoted);
    }

    private function changelog(string $unreleased): Changelog
    {
        return new Changelog("# Changelog\n\n## [Unreleased]\n\n{$unreleased}\n## [1.0.0] - 2026-01-01\n");
    }
}
