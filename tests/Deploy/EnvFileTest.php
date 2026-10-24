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

namespace Simplysmart\Tools\Tests\Deploy;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Simplysmart\Tools\Deploy\DeployConfigException;
use Simplysmart\Tools\Deploy\EnvFile;
use Simplysmart\Tools\Deploy\MissingConfigException;

/**
 * Parser plików KLUCZ=wartość: cudzysłowy, spacje, komentarze i czytelne błędy z numerem linii.
 */
final class EnvFileTest extends TestCase
{
    /**
     * @return iterable<string, array{0: string, 1: string}>
     */
    public static function values(): iterable
    {
        yield 'bez cudzysłowu' => ['KEY=value', 'value'];
        yield 'spacje wokół =' => ['KEY = value  ', 'value'];
        yield 'export' => ['export KEY=value', 'value'];
        yield 'pusta' => ['KEY=', ''];
        yield 'komentarz po wartości' => ['KEY=value # opis', 'value'];
        yield 'sam komentarz' => ['KEY= # opis', ''];
        yield '# wewnątrz wartości' => ['KEY=a#b', 'a#b'];
        yield 'podwójny cudzysłów ze spacjami' => ['KEY="Jan Kowalski"', 'Jan Kowalski'];
        yield 'podwójny z ucieczkami' => ['KEY="Anna \"Ania\" Nowak \\\\ x"', 'Anna "Ania" Nowak \\ x'];
        yield 'podwójny z apostrofem i #' => ['KEY="O\'Brien # nie komentarz"', "O'Brien # nie komentarz"];
        yield 'pojedynczy dosłownie' => ["KEY='\$(rm -rf ~) \\n \"x\"'", '$(rm -rf ~) \n "x"'];
        yield 'cudzysłów i komentarz' => ['KEY = "a b" # opis', 'a b'];
        yield 'polskie znaki' => ['KEY="Zażółć Gęślą"', 'Zażółć Gęślą'];
    }

    #[Test]
    #[DataProvider('values')]
    public function czyta_wartosc(string $line, string $expected): void
    {
        $this->assertSame(['KEY' => $expected], EnvFile::parse($line, 'deploy.env'));
    }

    #[Test]
    public function pomija_komentarze_i_puste_linie_a_powtorzony_klucz_nadpisuje(): void
    {
        $values = EnvFile::parse("# opis\n\n  # wcięty\r\nA=1\nB=2\nA=3\n", 'deploy.env');

        $this->assertSame(['A' => '3', 'B' => '2'], $values);
    }

    /**
     * @return iterable<string, array{0: string, 1: string}>
     */
    public static function errors(): iterable
    {
        yield 'niezamknięty podwójny' => ["A=1\nKEY=\"abc", 'deploy.env, linia 2: niezamknięty cudzysłów "'];
        yield 'niezamknięty pojedynczy' => ["KEY='abc", "deploy.env, linia 1: niezamknięty cudzysłów '"];
        yield 'tekst po cudzysłowie' => ['KEY="a" b', 'deploy.env, linia 1: tekst po zamykającym cudzysłowie.'];
        yield 'bez =' => ["A=1\n\nsamo słowo", 'deploy.env, linia 3: oczekiwano KLUCZ=wartość.'];
        yield 'zły klucz' => ['1KEY=x', 'deploy.env, linia 1: oczekiwano KLUCZ=wartość.'];
    }

    #[Test]
    #[DataProvider('errors')]
    public function zglasza_bledna_skladnie(string $contents, string $message): void
    {
        $this->expectException(DeployConfigException::class);
        $this->expectExceptionMessage($message);

        EnvFile::parse($contents, 'deploy.env');
    }

    #[Test]
    public function brak_pliku_wskazuje_wzor(): void
    {
        $this->expectException(MissingConfigException::class);
        $this->expectExceptionMessage('Brak infrastructure/deploy.env — skopiuj infrastructure/deploy.env.example i uzupełnij.');

        EnvFile::read(sys_get_temp_dir() . '/brak-' . bin2hex(random_bytes(4)), 'infrastructure/deploy.env');
    }
}
