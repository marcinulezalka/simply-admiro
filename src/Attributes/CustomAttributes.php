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
 * Globalne atrybuty rekordów danych dodawane przez użytkownika (x-admiro::attributes-editor, js/attributes-editor.js):
 * lista wierszy grupa – nazwa – wartość w `json_attributes.custom` dowolnego modelu z kolumną `json_attributes`
 * (pozostałe klucze — dane systemowe pakietów — zostają). Zapis przez model, więc zmiana trafia do historii tabeli
 * jednym wpisem. Pole formularza FIELD (`custom_attributes[i][group|name|value]`, kolejność wierszy = kolejność
 * atrybutów); wiersz bez nazwy jest pomijany, nazwa i grupa do NAME_MAX, wartość do VALUE_MAX, najwyżej MAX_ROWS.
 */
final class CustomAttributes
{
    /** Klucz listy atrybutów w `json_attributes`. */
    public const string KEY = 'custom';

    /** Pole formularza z wierszami atrybutów. */
    public const string FIELD = 'custom_attributes';

    public const int MAX_ROWS = 50;

    public const int NAME_MAX = 100;

    public const int VALUE_MAX = 1000;

    /**
     * Atrybuty rekordu w zapisanej kolejności.
     *
     * @return list<CustomAttribute>
     */
    public static function of(Model $model): array
    {
        $attributes = $model->getAttribute(ColumnNames::JSON_ATTRIBUTES);
        $rows = is_array($attributes) && is_array($attributes[self::KEY] ?? null) ? $attributes[self::KEY] : [];

        return self::fromRows($rows);
    }

    /**
     * Reguły walidacji pola FIELD (Laravel Validator).
     *
     * @return array<string, list<string>>
     */
    public static function rules(): array
    {
        return [
            self::FIELD => ['nullable', 'array', 'max:' . self::MAX_ROWS],
            self::FIELD . '.*' => ['array'],
            self::FIELD . '.*.group' => ['nullable', 'string', 'max:' . self::NAME_MAX],
            self::FIELD . '.*.name' => ['nullable', 'string', 'max:' . self::NAME_MAX],
            self::FIELD . '.*.value' => ['nullable', 'string', 'max:' . self::VALUE_MAX],
        ];
    }

    /**
     * Atrybuty z wierszy formularza albo zapisu: tekst przycięty, pusty — null, wiersz bez nazwy pominięty.
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
     * Zapisuje atrybuty rekordu (pusta lista usuwa klucz); bez zmian nic nie zapisuje.
     *
     * @param list<CustomAttribute> $attributes
     *
     * @return bool Czy atrybuty się zmieniły.
     */
    public function save(Model $model, array $attributes): bool
    {
        if (self::arrays(self::of($model)) === self::arrays($attributes)) {
            return false;
        }

        $stored = $model->getAttribute(ColumnNames::JSON_ATTRIBUTES);
        $stored = is_array($stored) ? $stored : [];
        unset($stored[self::KEY]);

        if ($attributes !== []) {
            $stored[self::KEY] = self::arrays($attributes);
        }

        $model->forceFill([ColumnNames::JSON_ATTRIBUTES => $stored === [] ? null : $stored])->save();

        return true;
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
