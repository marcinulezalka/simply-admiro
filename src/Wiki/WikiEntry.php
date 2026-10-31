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

namespace Simplysmart\Admiro\Wiki;

use Illuminate\Support\Str;

/**
 * Wpis słownika pojęć (wiki): identyfikator z prefiksem `wiki-` (kotwica i znacznik wpisu), ikona, słowo kluczowe,
 * definicja w ujęciu systemu, opcjonalny przykład, opcjonalne pytanie (Q&A — definicja jest wtedy odpowiedzią)
 * i opcjonalna instrukcja „Jak korzystać” (kroki pracy z pojęciem w aplikacji).
 */
final readonly class WikiEntry
{
    public const string PREFIX = 'wiki-';

    public function __construct(
        public string $key,
        public string $icon,
        public string $term,
        public string $definition,
        public ?string $example = null,
        public ?string $question = null,
        public ?string $usage = null,
    ) {}

    /**
     * Czy wpis pasuje do szukanego tekstu (słowo kluczowe, definicja, przykład, pytanie, instrukcja; bez wielkości liter
     * i polskich znaków).
     */
    public function matches(string $term): bool
    {
        $needle = Str::lower(Str::ascii(trim($term)));

        if ($needle === '') {
            return true;
        }

        $haystack = Str::lower(Str::ascii(implode(' ', array_filter([$this->key, $this->term, $this->definition, $this->example, $this->question, $this->usage]))));

        return str_contains($haystack, $needle);
    }

    /**
     * Identyfikator wpisu z prefiksem (np. `wiki-organizacja`): id elementu na stronie i znacznik przy wpisie.
     */
    public function id(): string
    {
        return self::PREFIX . $this->key;
    }
}
