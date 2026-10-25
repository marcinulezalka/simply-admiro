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

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Simplysmart\Admiro\Tools\Release\ReleaseBump;
use Simplysmart\Admiro\Tools\Release\ReleaseException;
use Simplysmart\Admiro\Tools\Release\SemanticVersion;

final class SemanticVersionTest extends TestCase
{
    #[Test]
    public function podbija_wersje_zerujac_nizsze_skladowe(): void
    {
        $version = SemanticVersion::parse('1.2.3');

        $this->assertSame('1.2.4', (string) $version->bump(ReleaseBump::Patch));
        $this->assertSame('1.3.0', (string) $version->bump(ReleaseBump::Minor));
        $this->assertSame('2.0.0', (string) $version->bump(ReleaseBump::Major));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidVersions(): array
    {
        return [
            'prefiks v' => ['v1.2.3'],
            'pre-release' => ['1.2.3-beta.1'],
            'zero wiodące' => ['01.2.3'],
            'brak składowej' => ['1.2'],
        ];
    }

    #[Test]
    #[DataProvider('invalidVersions')]
    public function odrzuca_niepoprawna_wersje(string $value): void
    {
        $this->expectException(ReleaseException::class);

        SemanticVersion::parse($value);
    }
}
