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

namespace Simplysmart\Tools\Release;

use DateTimeImmutable;

/**
 * Treść CHANGELOG.md w formacie Keep a Changelog: odczyt sekcji „[Unreleased]”,
 * ustalenie wymaganego podbicia i przeniesienie wpisów do datowanej sekcji wydania.
 */
final readonly class Changelog
{
    public const string UNRELEASED_TITLE = '[Unreleased]';

    private const string BREAKING_PATTERN = '/^[ \t]*[-*][ \t]+(?:\*\*BREAKING\*\*|BREAKING(?: CHANGE)?:)/m';

    /**
     * @param  string  $contents  Pełna treść CHANGELOG.md.
     */
    public function __construct(
        public string $contents,
    ) {}

    /**
     * Zwraca treść sekcji „## [Unreleased]”.
     *
     * @return string Treść między nagłówkiem „## [Unreleased]” a kolejnym nagłówkiem „##”.
     *
     * @throws ReleaseException Gdy w changelogu brakuje sekcji „## [Unreleased]”.
     */
    public function unreleased(): string
    {
        [, $sections] = self::splitByHeading($this->contents, 2);

        return $sections[self::UNRELEASED_TITLE]
            ?? throw new ReleaseException('W CHANGELOG.md brakuje sekcji „## ' . self::UNRELEASED_TITLE . '”.');
    }

    /**
     * Ustala podbicie wersji wymagane przez wpisy z sekcji „[Unreleased]”.
     *
     * Wygrywa najwyższe podbicie wśród niepustych sekcji; wpis oznaczony „**BREAKING**”
     * lub „BREAKING:” na początku linii wymusza MAJOR niezależnie od sekcji.
     *
     * @return ReleaseBump|null Najwyższe wymagane podbicie albo null, gdy żadna sekcja nie ma wpisów.
     *
     * @throws ReleaseException Gdy wpisy leżą poza nagłówkiem „###” albo nagłówek nie należy do Keep a Changelog.
     */
    public function requiredBump(): ?ReleaseBump
    {
        [$loose, $sections] = self::splitByHeading($this->unreleased(), 3);

        if (trim($loose) !== '') {
            throw new ReleaseException('Wpisy w „' . self::UNRELEASED_TITLE . '” muszą leżeć pod nagłówkiem „### <sekcja>”.');
        }

        $bump = null;

        foreach ($sections as $title => $body) {
            $sectionBump = ReleaseBump::forSection((string) $title) ?? throw new ReleaseException(
                "Nieznana sekcja „### {$title}”. Dozwolone: " . implode(', ', array_keys(ReleaseBump::SECTIONS)) . '.',
            );

            if (trim($body) === '') {
                continue;
            }

            $entryBump = preg_match(self::BREAKING_PATTERN, $body) === 1 ? ReleaseBump::Major : $sectionBump;
            $bump = $entryBump->max($bump);
        }

        return $bump;
    }

    /**
     * Zamienia „## [Unreleased]” na datowaną sekcję wydania z nową, pustą sekcją „[Unreleased]” nad nią.
     *
     * @param  SemanticVersion  $version  Wydawana wersja.
     * @param  DateTimeImmutable  $releasedAt  Moment wydania (liczy się data).
     * @return string Nowa treść CHANGELOG.md, z zachowanym stylem końców linii.
     *
     * @throws ReleaseException Gdy podmiana nagłówka się nie powiodła.
     */
    public function promote(SemanticVersion $version, DateTimeImmutable $releasedAt): string
    {
        $eol = str_contains($this->contents, "\r\n") ? "\r\n" : "\n";
        $heading = '## ' . self::UNRELEASED_TITLE;

        $promoted = preg_replace(
            self::headingPattern(2, preg_quote(self::UNRELEASED_TITLE, '/')),
            $heading . $eol . $eol . "## [{$version}] - {$releasedAt->format('Y-m-d')}",
            $this->contents,
            1,
        );

        return $promoted ?? throw new ReleaseException('Nie udało się przenieść wpisów z „' . self::UNRELEASED_TITLE . '”.');
    }

    /**
     * Buduje wyrażenie regularne dopasowujące linię nagłówka Markdown.
     *
     * @param  int  $level  Poziom nagłówka (2 dla „##”, 3 dla „###”).
     * @param  string  $titlePattern  Fragment regex dopasowujący tytuł (może zawierać grupę przechwytującą).
     * @return string Wyrażenie regularne w trybie wielolinijkowym; koniec linii (LF lub CRLF)
     *                nie wchodzi w dopasowanie, więc podmiana nagłówka go zachowuje.
     */
    private static function headingPattern(int $level, string $titlePattern): string
    {
        return '/^' . str_repeat('#', $level) . '[ \t]+' . $titlePattern . '[ \t]*(?=\r?$)/m';
    }

    /**
     * Dzieli tekst Markdown według nagłówków danego poziomu.
     *
     * @param  string  $markdown  Dzielony tekst.
     * @param  int  $level  Poziom nagłówka (2 dla „##”, 3 dla „###”).
     * @return array{0: string, 1: array<array-key, string>} Tekst przed pierwszym nagłówkiem
     *                                                       oraz mapa tytuł nagłówka → treść pod nim
     *                                                       (tytuł liczbowy PHP zamienia na klucz int).
     */
    private static function splitByHeading(string $markdown, int $level): array
    {
        $parts = preg_split(self::headingPattern($level, '(.+?)'), $markdown, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [$markdown];
        $preamble = (string) array_shift($parts);
        $sections = [];

        foreach (array_chunk($parts, 2) as $pair) {
            [$title, $body] = $pair + [1 => ''];
            $sections[$title] = ($sections[$title] ?? '') . $body;
        }

        return [$preamble, $sections];
    }
}
