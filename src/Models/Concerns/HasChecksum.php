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

namespace Simplysmart\Admiro\Models\Concerns;

use BackedEnum;
use DateTimeInterface;
use Simplysmart\Admiro\Database\ColumnNames;

/**
 * `checksum` = SHA-256 kanonicznego JSON kolumn domenowych (docs/baza-danych.md §10), liczona przy
 * każdym zapisie; kolumny techniczne (ColumnNames::CHECKSUM_EXCLUDED) jej nie zmieniają, a null liczy się
 * jak brak wartości. Kolumny z wartością domyślną w bazie muszą ją mieć też w `$attributes` modelu.
 *
 * @method static void saving(\Closure|string $callback) Metoda modelu Eloquent.
 * @method array<string, mixed> getAttributes() Metoda modelu Eloquent.
 * @method mixed getAttributeValue(string $key) Metoda modelu Eloquent.
 * @method $this setAttribute(string $key, mixed $value) Metoda modelu Eloquent.
 */
trait HasChecksum
{
    public static function bootHasChecksum(): void
    {
        static::saving(static function (self $model): void {
            $model->setAttribute(ColumnNames::CHECKSUM, $model->computeChecksum());
        });
    }

    public function computeChecksum(): string
    {
        $values = [];

        foreach (array_keys($this->getAttributes()) as $column) {
            $value = $this->getAttributeValue($column);

            if ($value !== null && ! in_array($column, ColumnNames::CHECKSUM_EXCLUDED, true)) {
                $values[$column] = self::canonical($value);
            }
        }

        ksort($values);

        return hash('sha256', (string) json_encode($values, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION));
    }

    /**
     * Wartość niezależna od typu odczytu: daty jako tekst, enumy jako wartość, tablice posortowane.
     */
    private static function canonical(mixed $value): mixed
    {
        return match (true) {
            $value instanceof DateTimeInterface => $value->format('Y-m-d H:i:s'),
            $value instanceof BackedEnum => $value->value,
            is_array($value) => self::sorted($value),
            default => $value,
        };
    }

    /**
     * @param array<array-key, mixed> $values
     *
     * @return array<array-key, mixed>
     */
    private static function sorted(array $values): array
    {
        if (! array_is_list($values)) {
            ksort($values);
        }

        return array_map(self::canonical(...), $values);
    }
}
