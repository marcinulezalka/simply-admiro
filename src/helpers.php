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

use Illuminate\Container\Container;
use Simplysmart\Mainboard\Contracts\LocalFormatter;
use Simplysmart\Mainboard\Contracts\TextTranslator;
use Simplysmart\Mainboard\Translation\TextGroup;

if (! function_exists('text')) {
    /**
     * Tłumaczenie kaskadowe: paczka strefy → mainboard → skeleton → klucz (bez języka zapasowego).
     *
     * @param  array<string, mixed>  $replace
     */
    function text(string $key, array $replace = [], ?string $locale = null): string
    {
        return Container::getInstance()->make(TextTranslator::class)->text($key, $replace, $locale);
    }
}

if (! function_exists('text_action')) {
    /**
     * Powtarzalna akcja interfejsu z grupy "actions", np. text_action('save') → „Zapisz”.
     *
     * @param  array<string, mixed>  $replace
     */
    function text_action(string $key, array $replace = [], ?string $locale = null): string
    {
        return Container::getInstance()->make(TextTranslator::class)->group(TextGroup::Actions, $key, $replace, $locale);
    }
}

if (! function_exists('text_data')) {
    /**
     * Nazwa kolumny modelu z grupy "data": data.<model>.<kolumna>, potem data.<kolumna>.
     *
     * @param  array<string, mixed>  $replace
     */
    function text_data(object|string $model, string $column, array $replace = [], ?string $locale = null): string
    {
        return Container::getInstance()->make(TextTranslator::class)->data($model, $column, $replace, $locale);
    }
}

if (! function_exists('text_file')) {
    /**
     * Etykieta pliku eksportu (CSV, Excel, PDF) z grupy "files".
     *
     * @param  array<string, mixed>  $replace
     */
    function text_file(string $key, array $replace = [], ?string $locale = null): string
    {
        return Container::getInstance()->make(TextTranslator::class)->group(TextGroup::Files, $key, $replace, $locale);
    }
}

if (! function_exists('text_email')) {
    /**
     * Treść wiadomości e-mail z grupy "emails".
     *
     * @param  array<string, mixed>  $replace
     */
    function text_email(string $key, array $replace = [], ?string $locale = null): string
    {
        return Container::getInstance()->make(TextTranslator::class)->group(TextGroup::Emails, $key, $replace, $locale);
    }
}

if (! function_exists('local_money')) {
    /**
     * Kwota z walutą sformatowana według języka; bez waluty używa config('mainboard.locale.currency').
     */
    function local_money(int|float $amount, ?string $currency = null, ?string $locale = null): string
    {
        return Container::getInstance()->make(LocalFormatter::class)->money($amount, $currency, $locale);
    }
}

if (! function_exists('local_number')) {
    /**
     * Liczba z separatorami właściwymi dla języka.
     */
    function local_number(int|float $number, int $precision = 0, ?string $locale = null): string
    {
        return Container::getInstance()->make(LocalFormatter::class)->number($number, $precision, $locale);
    }
}

if (! function_exists('local_date')) {
    /**
     * Data w formacie ISO Carbona, domyślnie "L" (20.10.2026 w pl).
     */
    function local_date(DateTimeInterface|string $date, ?string $format = null, ?string $locale = null): string
    {
        return Container::getInstance()->make(LocalFormatter::class)->date($date, $format, $locale);
    }
}

if (! function_exists('local_day')) {
    /**
     * Nazwa dnia tygodnia; liczba to dzień ISO 1–7 (1 = poniedziałek).
     */
    function local_day(int|DateTimeInterface $day, ?string $format = null, ?string $locale = null): string
    {
        return Container::getInstance()->make(LocalFormatter::class)->day($day, $format, $locale);
    }
}

if (! function_exists('local_month')) {
    /**
     * Nazwa miesiąca w mianowniku; liczba to miesiąc 1–12.
     */
    function local_month(int|DateTimeInterface $month, ?string $format = null, ?string $locale = null): string
    {
        return Container::getInstance()->make(LocalFormatter::class)->month($month, $format, $locale);
    }
}
