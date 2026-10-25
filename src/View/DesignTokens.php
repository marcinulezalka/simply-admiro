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

namespace Simplysmart\Admiro\View;

/**
 * Tokeny design systemu odczytane z CSS (`:root`, wartości `light-dark(jasny, ciemny)` — także wewnątrz złożonych
 * wartości jak cień — i dawny blok `:root` w `prefers-color-scheme: dark`), z rozwiązanymi odwołaniami `var(--...)`. Strona design systemu pokazuje je bez własnej listy, więc `tokens.css` zostaje
 * jedynym źródłem wartości.
 */
final readonly class DesignTokens
{
    private const string PREFIX = '--ss-';

    private const int MAX_REFERENCE_DEPTH = 8;

    /**
     * @param array<string, string> $light Wartości motywu jasnego (nazwa właściwości → wartość).
     * @param array<string, string> $dark Wartości motywu ciemnego (jasne nadpisane blokiem ciemnym).
     */
    private function __construct(
        private array $light,
        private array $dark,
    ) {}

    public static function fromCss(string $css): self
    {
        $css = (string) preg_replace('~/\*.*?\*/~s', '', $css);
        $darkStart = strpos($css, '@media');
        $light = self::declarations(self::rootBlock($darkStart === false ? $css : substr($css, 0, $darkStart)));
        $dark = preg_match('~@media\s*\(\s*prefers-color-scheme\s*:\s*dark\s*\)\s*\{(.*)~s', $css, $match) === 1
            ? [...$light, ...self::declarations(self::rootBlock($match[1]))]
            : $light;

        return new self($light, $dark);
    }

    /**
     * Tokeny jednej grupy (`color`, `radius`, `shadow`, `space`, `font`…) w kolejności z pliku.
     *
     * @return list<DesignToken>
     */
    public function group(string $group): array
    {
        $prefix = self::PREFIX . $group . '-';
        $tokens = [];

        foreach (array_keys($this->light) as $name) {
            if (str_starts_with($name, $prefix)) {
                $tokens[] = new DesignToken($name, substr($name, strlen($prefix)), $this->resolve($name, $this->light, 0), $this->resolve($name, $this->dark, 1));
            }
        }

        return $tokens;
    }

    private static function rootBlock(string $css): string
    {
        return preg_match('~:root\s*\{([^}]*)\}~', $css, $match) === 1 ? $match[1] : '';
    }

    /**
     * @return array<string, string>
     */
    private static function declarations(string $block): array
    {
        preg_match_all('~(--[a-z0-9-]+)\s*:\s*([^;]+);~i', $block, $matches, PREG_SET_ORDER);
        $values = [];

        foreach ($matches as $match) {
            $values[$match[1]] = trim($match[2]);
        }

        return $values;
    }

    /**
     * Wartość tokenu w motywie: wariant `light-dark()` (0 jasny, 1 ciemny) i łańcuch `var()` aż do wartości końcowej.
     *
     * @param array<string, string> $values
     */
    private function resolve(string $name, array $values, int $variant): string
    {
        $value = self::variant($values[$name] ?? '', $variant);

        for ($depth = 0; $depth < self::MAX_REFERENCE_DEPTH && preg_match('~^var\(\s*(--[a-z0-9-]+)\s*\)$~i', $value, $match) === 1; $depth++) {
            $value = self::variant($values[$match[1]] ?? $value, $variant);
        }

        return $value;
    }

    /**
     * Zastępuje każde `light-dark(a, b)` w wartości wybranym argumentem (przecinek liczony poza nawiasami).
     */
    private static function variant(string $value, int $variant): string
    {
        $marker = 'light-dark(';

        while (($start = stripos($value, $marker)) !== false) {
            $depth = 0;
            $split = null;
            $end = null;

            for ($i = $start + strlen($marker); $i < strlen($value); $i++) {
                $char = $value[$i];

                if ($char === '(') {
                    $depth++;
                } elseif ($char === ')' && $depth > 0) {
                    $depth--;
                } elseif ($char === ')') {
                    $end = $i;
                    break;
                } elseif ($char === ',' && $depth === 0) {
                    $split ??= $i;
                }
            }

            if ($end === null || $split === null) {
                return $value;
            }

            $inner = $start + strlen($marker);
            $chosen = $variant === 0 ? substr($value, $inner, $split - $inner) : substr($value, $split + 1, $end - $split - 1);
            $value = substr($value, 0, $start) . trim($chosen) . substr($value, $end + 1);
        }

        return $value;
    }
}
