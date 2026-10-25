<?php

declare(strict_types=1);

/**
 * This file is part of simplysmart/mainboard.
 *
 * Copyright (c) 2026 Marcin Ulezalka <marcin.ulezalka@simplysmart.pl>
 *
 * Licensed under the Simplysmart Restricted Source License 1.0.
 * See the LICENSE file distributed with this source code.
 */

namespace Simplysmart\Mainboard\Models\Concerns;

use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Simplysmart\Mainboard\Database\ColumnNames;

/**
 * Krótkie tłumaczenia słownika w `json_i18n` (`{"pl": "polski", "en": "Polish"}`, docs/baza-danych.md §7).
 * Brak tłumaczenia w danym języku nie spada na inny język: zwraca translationFallback() (zwykle `code`).
 *
 * @method void mergeCasts(array<string, string> $casts) Metoda modelu Eloquent.
 * @method mixed getAttribute(string $key) Metoda modelu Eloquent.
 */
trait HasI18n
{
    /**
     * Tekst pokazywany, gdy tłumaczenia brak (klucz rekordu, np. kod ISO).
     */
    abstract public function translationFallback(): string;

    public function initializeHasI18n(): void
    {
        $this->mergeCasts([ColumnNames::JSON_I18N => 'array']);
    }

    /**
     * Nazwa w języku bieżącym (albo podanym).
     */
    public function translatedName(?string $locale = null): string
    {
        $translations = $this->getAttribute(ColumnNames::JSON_I18N);
        $locale ??= Container::getInstance()->make(Application::class)->getLocale();
        $name = is_array($translations) ? ($translations[$locale] ?? null) : null;

        return is_string($name) && $name !== '' ? $name : $this->translationFallback();
    }
}
