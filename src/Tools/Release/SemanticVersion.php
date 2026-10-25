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

namespace Simplysmart\Admiro\Tools\Release;

use Stringable;

/**
 * Wersja w formacie MAJOR.MINOR.PATCH (bez prefiksu „v” i sufiksów pre-release).
 */
final readonly class SemanticVersion implements Stringable
{
    private const string PATTERN = '/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)$/';

    public function __construct(
        public int $major,
        public int $minor,
        public int $patch,
    ) {}

    /**
     * Tworzy wersję z tekstu, np. zawartości pliku VERSION.
     *
     * @param  string  $value  Tekst wersji, np. „1.4.0”.
     * @return SemanticVersion Odczytana wersja.
     *
     * @throws ReleaseException Gdy tekst nie jest poprawną wersją MAJOR.MINOR.PATCH.
     */
    public static function parse(string $value): self
    {
        if (! preg_match(self::PATTERN, $value, $matches)) {
            throw new ReleaseException(
                "Plik VERSION zawiera „{$value}” — oczekiwano formatu MAJOR.MINOR.PATCH (np. 1.4.0), bez prefiksu „v” i sufiksów pre-release.",
            );
        }

        return new self((int) $matches[1], (int) $matches[2], (int) $matches[3]);
    }

    /**
     * Zwraca nową wersję podbitą o wskazany poziom.
     *
     * @param  ReleaseBump  $bump  Poziom podbicia.
     * @return SemanticVersion Nowa wersja; niższe składowe są zerowane.
     */
    public function bump(ReleaseBump $bump): self
    {
        return match ($bump) {
            ReleaseBump::Major => new self($this->major + 1, 0, 0),
            ReleaseBump::Minor => new self($this->major, $this->minor + 1, 0),
            ReleaseBump::Patch => new self($this->major, $this->minor, $this->patch + 1),
        };
    }

    public function __toString(): string
    {
        return "{$this->major}.{$this->minor}.{$this->patch}";
    }
}
