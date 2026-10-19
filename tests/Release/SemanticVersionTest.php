<?php

declare(strict_types=1);

namespace Simplysmart\Tools\Tests\Release;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Simplysmart\Tools\Release\ReleaseBump;
use Simplysmart\Tools\Release\ReleaseException;
use Simplysmart\Tools\Release\SemanticVersion;

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
