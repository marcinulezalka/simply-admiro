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
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Simplysmart\Admiro\Tools\Release\Changelog;
use Simplysmart\Admiro\Tools\Release\ReleaseBump;
use Simplysmart\Admiro\Tools\Release\ReleaseException;
use Simplysmart\Admiro\Tools\Release\ReleasePlan;
use Simplysmart\Admiro\Tools\Release\ReleasePlanner;
use Simplysmart\Admiro\Tools\Release\SemanticVersion;

final class ReleasePlannerTest extends TestCase
{
    #[Test]
    public function brak_wpisow_oznacza_brak_wydania(): void
    {
        $this->assertNull($this->plan("### Added\n", false));
    }

    #[Test]
    public function major_wymaga_potwierdzenia(): void
    {
        $this->expectException(ReleaseException::class);
        $this->expectExceptionMessage('--major-confirm');

        $this->plan("### Removed\n\n- Usunięto X.\n", false);
    }

    #[Test]
    public function potwierdzony_major_podbija_wersje(): void
    {
        $plan = $this->plan("### Removed\n\n- Usunięto X.\n", true);
        $this->assertNotNull($plan);

        $this->assertSame(ReleaseBump::Major, $plan->bump);
        $this->assertSame('2.0.0', (string) $plan->to);
        $this->assertStringContainsString('## [2.0.0] - 2026-10-19', $plan->changelog);
    }

    private function plan(string $unreleased, bool $majorConfirmed): ?ReleasePlan
    {
        return (new ReleasePlanner())->plan(
            new Changelog("# Changelog\n\n## [Unreleased]\n\n{$unreleased}\n## [1.0.0] - 2026-01-01\n"),
            new SemanticVersion(1, 0, 0),
            new DateTimeImmutable('2026-10-19'),
            $majorConfirmed,
        );
    }
}
