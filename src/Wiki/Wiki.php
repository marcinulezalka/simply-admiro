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

/**
 * Słownik pojęć (singleton): wpisy z ikoną i tekstami `<przestrzeń>wiki.<klucz>.term|definition|example` (kaskada
 * tekstów: paczka strefy, admiro, aplikacja — więc paczka albo aplikacja może podać własne ujęcie pojęcia; opcjonalnie
 * `.question` — wpis Q&A, i `.usage` — „Jak korzystać”). Core
 * rejestruje pojęcia wspólne (organizacja, strefa, departament, właściciel, organizacja operatora); paczka dopisuje
 * swoje w providerze (add(), z przestrzenią tekstów paczki). Wpisy pokazuje x-admiro::wiki-tip (porady, Q&A).
 */
final class Wiki
{
    /**
     * @var array<string, array{icon: string, texts: string}>
     */
    private array $entries = [];

    /**
     * @param string $key Klucz bez prefiksu, małe litery i myślniki (np. `organizacja`).
     * @param string $texts Przestrzeń tekstów paczki z `::` (np. `shop::`) albo pusta — teksty Core.
     */
    public function add(string $key, string $icon, string $texts = ''): void
    {
        $this->entries[$key] = ['icon' => $icon, 'texts' => $texts];
    }

    public function has(string $key): bool
    {
        return isset($this->entries[$key]);
    }

    public function entry(string $key): ?WikiEntry
    {
        $entry = $this->entries[$key] ?? null;

        if ($entry === null) {
            return null;
        }

        $text = static fn(string $part): string => text($entry['texts'] . 'wiki.' . $key . '.' . $part);
        $optional = static function (string $part) use ($entry, $key): ?string {
            $textKey = $entry['texts'] . 'wiki.' . $key . '.' . $part;
            $value = text($textKey);

            return $value !== $textKey ? $value : null;
        };

        return new WikiEntry($key, $entry['icon'], $text('term'), $text('definition'), $optional('example'), $optional('question'), $optional('usage'));
    }

    /**
     * Wpisy pasujące do szukanego tekstu (WikiEntry::matches()); pusty tekst — wszystkie.
     *
     * @return list<WikiEntry>
     */
    public function search(string $term): array
    {
        return array_values(array_filter($this->all(), static fn(WikiEntry $entry): bool => $entry->matches($term)));
    }

    /**
     * Wszystkie wpisy w kolejności dodania.
     *
     * @return list<WikiEntry>
     */
    public function all(): array
    {
        return array_values(array_filter(array_map($this->entry(...), array_keys($this->entries))));
    }
}
