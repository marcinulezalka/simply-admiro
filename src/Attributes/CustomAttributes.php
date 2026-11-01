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

namespace Simplysmart\Admiro\Attributes;

use Illuminate\Database\Eloquent\Model;
use Simplysmart\Admiro\Database\ColumnNames;

/**
 * Pola dodatkowe rekordów danych dodawane przez użytkownika (x-admiro::attributes-editor, js/attributes-editor.js):
 * w `json_attributes.custom` dowolnego modelu z kolumną `json_attributes`, osobno dla każdej sekcji rekordu (np. karty
 * danych, adresu i kontaktu organizacji: sekcja → lista wierszy grupa – nazwa – wartość); pozostałe klucze
 * `json_attributes` — dane systemowe pakietów — zostają. Dawna płaska lista należy do sekcji DEFAULT_SECTION. Zapis
 * przez model, więc zmiana trafia do historii tabeli jednym wpisem. Pole formularza FIELD
 * (`custom_attributes[<sekcja>][i][group|name|value]`, kolejność wierszy = kolejność pól); wiersz bez nazwy jest
 * pomijany, nazwa i grupa do NAME_MAX, wartość do VALUE_MAX, najwyżej MAX_ROWS na sekcję, klucz sekcji jak
 * SECTION_PATTERN.
 */
final class CustomAttributes
{
    /** Klucz pól dodatkowych w `json_attributes`. */
    public const string KEY = 'custom';

    /** Pole formularza z sekcjami wierszy. */
    public const string FIELD = 'custom_attributes';

    /** Sekcja rekordu bez podziału na karty (i dawnej płaskiej listy). */
    public const string DEFAULT_SECTION = 'default';

    public const string SECTION_PATTERN = '/^[a-z][a-z0-9-]*$/';

    public const int MAX_ROWS = 50;

    public const int NAME_MAX = 100;

    public const int VALUE_MAX = 1000;

    /**
     * Pola dodatkowe sekcji rekordu w zapisanej kolejności.
     *
     * @return list<CustomAttribute>
     */
    public static function of(Model $model, string $section = self::DEFAULT_SECTION): array
    {
        return self::fromRows(self::stored($model)[$section] ?? []);
    }

    /**
     * Reguły walidacji pola FIELD (Laravel Validator).
     *
     * @return array<string, list<string>>
     */
    public static function rules(): array
    {
        return [
            self::FIELD => ['nullable', 'array'],
            self::FIELD . '.*' => ['nullable', 'array', 'max:' . self::MAX_ROWS],
            self::FIELD . '.*.*' => ['array'],
            self::FIELD . '.*.*.group' => ['nullable', 'string', 'max:' . self::NAME_MAX],
            self::FIELD . '.*.*.name' => ['nullable', 'string', 'max:' . self::NAME_MAX],
            self::FIELD . '.*.*.value' => ['nullable', 'string', 'max:' . self::VALUE_MAX],
        ];
    }

    /**
     * Sekcje z formularza (wartość pola FIELD): klucz sekcji → pola dodatkowe; klucz spoza SECTION_PATTERN pominięty,
     * sekcja bez wierszy — pusta lista (usunięcie jej pól).
     *
     * @return array<string, list<CustomAttribute>>
     */
    public static function fromInput(mixed $input): array
    {
        if (! is_array($input)) {
            return [];
        }

        $sections = [];

        foreach ($input as $section => $rows) {
            if (is_string($section) && preg_match(self::SECTION_PATTERN, $section) === 1) {
                $sections[$section] = self::fromRows($rows);
            }
        }

        return $sections;
    }

    /**
     * Pola dodatkowe z wierszy formularza albo zapisu: tekst przycięty, pusty — null, wiersz bez nazwy pominięty.
     *
     * @return list<CustomAttribute>
     */
    public static function fromRows(mixed $rows): array
    {
        if (! is_array($rows)) {
            return [];
        }

        $attributes = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $name = self::text($row['name'] ?? null);

            if ($name !== null) {
                $attributes[] = new CustomAttribute(self::text($row['group'] ?? null), $name, self::text($row['value'] ?? null));
            }
        }

        return $attributes;
    }

    /**
     * Zapisuje podane sekcje rekordu (pozostałe sekcje zostają, pusta lista usuwa sekcję); bez zmian nic nie zapisuje.
     *
     * @param array<string, list<CustomAttribute>> $sections
     *
     * @return bool Czy pola dodatkowe się zmieniły.
     */
    public function save(Model $model, array $sections): bool
    {
        $current = array_filter(array_map(static fn(mixed $rows): array => self::arrays(self::fromRows($rows)), self::stored($model)));
        $next = $current;

        foreach ($sections as $section => $attributes) {
            if ($attributes === []) {
                unset($next[$section]);
            } else {
                $next[$section] = self::arrays($attributes);
            }
        }

        if ($next === $current && self::stored($model) === $current) {
            return false;
        }

        $stored = $model->getAttribute(ColumnNames::JSON_ATTRIBUTES);
        $stored = is_array($stored) ? $stored : [];
        unset($stored[self::KEY]);

        if ($next !== []) {
            $stored[self::KEY] = $next;
        }

        $model->forceFill([ColumnNames::JSON_ATTRIBUTES => $stored === [] ? null : $stored])->save();

        return true;
    }

    /**
     * Zapisane sekcje rekordu (sekcja → surowe wiersze); dawna płaska lista to DEFAULT_SECTION.
     *
     * @return array<array-key, mixed>
     */
    private static function stored(Model $model): array
    {
        $attributes = $model->getAttribute(ColumnNames::JSON_ATTRIBUTES);
        $custom = is_array($attributes) ? ($attributes[self::KEY] ?? null) : null;

        if (! is_array($custom)) {
            return [];
        }

        return array_is_list($custom) ? [self::DEFAULT_SECTION => $custom] : $custom;
    }

    /**
     * @param list<CustomAttribute> $attributes
     *
     * @return list<array{group: ?string, name: string, value: ?string}>
     */
    private static function arrays(array $attributes): array
    {
        return array_map(static fn(CustomAttribute $attribute): array => $attribute->toArray(), $attributes);
    }

    private static function text(mixed $value): ?string
    {
        $text = is_scalar($value) ? trim((string) $value) : '';

        return $text === '' ? null : $text;
    }
}
