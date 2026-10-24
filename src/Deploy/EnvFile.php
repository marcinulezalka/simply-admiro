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

namespace Simplysmart\Tools\Deploy;

/**
 * Parser plików `KLUCZ=wartość` (deploy.env, deploy.onboarding.env) bez uruchamiania powłoki.
 *
 * Obsługuje komentarze `#` i puste linie, opcjonalny prefiks `export `, spacje wokół `=`, wartości w cudzysłowie
 * pojedynczym (dosłownie) i podwójnym (`\"` i `\\` jako znaki) oraz komentarz po wartości. Powtórzony klucz
 * nadpisuje wcześniejszy (jak w bashu).
 */
final readonly class EnvFile
{
    /**
     * Przyrostek pliku wzoru obok konfiguracji (deploy.env → deploy.env.example).
     */
    public const string EXAMPLE_SUFFIX = '.example';

    private const string LINE = '/^(?:export\s+)?([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(.*)$/';

    private const string TRAILING_COMMENT = '/^\s*(#.*)?$/';

    /**
     * @param  string  $path  Pełna ścieżka pliku.
     * @param  string  $name  Nazwa pliku w komunikatach (np. ścieżka względem projektu).
     * @return array<string, string>
     *
     * @throws MissingConfigException Gdy pliku nie ma.
     * @throws DeployConfigException Gdy składnia jest błędna.
     */
    public static function read(string $path, string $name): array
    {
        if (! is_file($path)) {
            throw MissingConfigException::file($name);
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new DeployConfigException("Nie udało się odczytać {$name}.");
        }

        return self::parse($contents, $name);
    }

    /**
     * @return array<string, string>
     *
     * @throws DeployConfigException
     */
    public static function parse(string $contents, string $name): array
    {
        $values = [];

        foreach (preg_split('/\r\n|\n|\r/', $contents) ?: [] as $index => $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $number = $index + 1;

            if (preg_match(self::LINE, $line, $match) !== 1) {
                throw new DeployConfigException("{$name}, linia {$number}: oczekiwano KLUCZ=wartość.");
            }

            $values[$match[1]] = self::value($match[2], $name, $number);
        }

        return $values;
    }

    /**
     * @throws DeployConfigException
     */
    private static function value(string $raw, string $name, int $number): string
    {
        $quote = $raw[0] ?? '';

        if ($quote !== "'" && $quote !== '"') {
            $comment = preg_match('/(^|\s)#/', $raw, $match, PREG_OFFSET_CAPTURE) === 1 ? $match[0][1] : null;

            return rtrim($comment === null ? $raw : substr($raw, 0, $comment));
        }

        $value = '';
        $length = strlen($raw);

        for ($position = 1; $position < $length; $position++) {
            $char = $raw[$position];

            if ($char === $quote) {
                if (preg_match(self::TRAILING_COMMENT, substr($raw, $position + 1)) !== 1) {
                    throw new DeployConfigException("{$name}, linia {$number}: tekst po zamykającym cudzysłowie.");
                }

                return $value;
            }

            if ($quote === '"' && $char === '\\' && in_array($raw[$position + 1] ?? '', ['"', '\\'], true)) {
                $char = $raw[++$position];
            }

            $value .= $char;
        }

        throw new DeployConfigException("{$name}, linia {$number}: niezamknięty cudzysłów {$quote}.");
    }
}
