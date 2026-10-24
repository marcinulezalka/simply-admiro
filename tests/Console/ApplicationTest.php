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

namespace Simplysmart\Tools\Tests\Console;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Simplysmart\Tools\Console\Application;
use Simplysmart\Tools\Console\Command;
use Simplysmart\Tools\Console\PresetCommand;

/**
 * Wejście `simplysmart`: lista poleceń, wybór po nazwie (kropka = dwukropek), nieznane polecenie i preset.
 */
final class ApplicationTest extends TestCase
{
    /**
     * @var list<array{0: string, 1: list<string>}>
     */
    private array $calls = [];

    #[Test]
    public function bez_nazwy_pokazuje_liste_polecen(): void
    {
        [$exitCode, $stdout] = $this->runApplication([]);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Użycie: vendor/bin/simplysmart <polecenie> [opcje]', $stdout);
        $this->assertMatchesRegularExpression('/^  deploy:onboarding\s+Onboarding$/m', $stdout);
        $this->assertSame([], $this->calls);
    }

    #[Test]
    public function uruchamia_polecenie_takze_z_kropka(): void
    {
        [$colon] = $this->runApplication(['deploy:onboarding', '--dry-run']);
        [$dot] = $this->runApplication(['deploy.onboarding']);

        $this->assertSame([3, 3], [$colon, $dot]);
        $this->assertSame([['deploy:onboarding', ['--dry-run']], ['deploy:onboarding', []]], $this->calls);
    }

    #[Test]
    public function preset_dopisuje_argumenty_przed_argumentami_uzytkownika(): void
    {
        $this->runApplication(['deploy:lock', '--dry-run']);

        $this->assertSame([['deploy:onboarding', ['--deploy', '--dry-run']]], $this->calls);
    }

    #[Test]
    public function nieznane_polecenie_to_blad_z_lista(): void
    {
        [$exitCode, $stdout, $stderr] = $this->runApplication(['deplo']);

        $this->assertSame(1, $exitCode);
        $this->assertSame('', $stdout);
        $this->assertStringStartsWith("Nieznane polecenie: deplo\n", $stderr);
        $this->assertStringContainsString('deploy:onboarding', $stderr);
    }

    /**
     * @param  list<string>  $arguments
     * @return array{0: int, 1: string, 2: string}
     */
    private function runApplication(array $arguments): array
    {
        $command = new class implements Command {
            /**
             * @var list<array{0: string, 1: list<string>}>
             */
            public array $calls = [];

            public function name(): string
            {
                return 'deploy:onboarding';
            }

            public function description(): string
            {
                return 'Onboarding';
            }

            public function run(array $arguments, string $workingDirectory, $stdout, $stderr): int
            {
                $this->calls[] = [$this->name(), $arguments];

                return 3;
            }
        };
        $stdout = fopen('php://memory', 'w+');
        $stderr = fopen('php://memory', 'w+');
        $this->assertIsResource($stdout);
        $this->assertIsResource($stderr);

        $application = new Application([$command, new PresetCommand('deploy:lock', 'Lock', $command, ['--deploy'])]);
        $exitCode = $application->run($arguments, '/tmp', $stdout, $stderr);
        $this->calls = [...$this->calls, ...$command->calls];

        return [$exitCode, (string) stream_get_contents($stdout, -1, 0), (string) stream_get_contents($stderr, -1, 0)];
    }
}
