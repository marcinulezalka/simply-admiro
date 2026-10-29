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

namespace Simplysmart\Admiro\Organizations;

use Illuminate\Database\Eloquent\Collection;
use Simplysmart\Admiro\Models\Dictionaries\Country;
use Simplysmart\Admiro\Models\Organization;
use Simplysmart\Admiro\Queries\Dictionaries\DefaultEntries;
use Simplysmart\Admiro\Queries\Dictionaries\LocalizationChoices;

/**
 * Kraje w danych kontaktowych organizacji: kraj adresu (aktywne kraje słownika, w nowym wpisie kraj domyślny
 * aplikacji) i prefiksy telefonów (`locale_countries.dial_code`). Telefon zapisuje się z prefiksem
 * (`+48 22 100 20 30`); do edycji rozbija się na kraj prefiksu i numer — przy prefiksie wspólnym dla kilku krajów
 * (np. +1) wygrywa kraj organizacji, potem kraj domyślny.
 */
final class ContactCountries
{
    /**
     * Pole formularza z krajem prefiksu telefonu (`<kolumna telefonu>_country`).
     */
    public const string PREFIX_FIELD_SUFFIX = '_country';

    /**
     * @var Collection<int, Country>|null
     */
    private ?Collection $countries = null;

    public function __construct(
        private readonly LocalizationChoices $choices,
        private readonly DefaultEntries $defaults,
    ) {}

    public static function prefixField(string $phoneColumn): string
    {
        return $phoneColumn . self::PREFIX_FIELD_SUFFIX;
    }

    /**
     * Aktywne kraje słownika (kolejność słownika).
     *
     * @return Collection<int, Country>
     */
    public function countries(): Collection
    {
        return $this->countries ??= $this->choices->countries();
    }

    /**
     * Kody alpha-2 aktywnych krajów (walidacja kraju adresu i prefiksu).
     *
     * @return list<string>
     */
    public function codes(): array
    {
        return array_values(array_map(static fn(Country $country): string => (string) $country->alpha_code_2, $this->countries()->all()));
    }

    /**
     * Kraj zaznaczony w formularzu: kraj organizacji, a w nowym wpisie (albo bez kraju) kraj domyślny aplikacji.
     */
    public function selectedCode(?Organization $organization): ?string
    {
        return $organization->country_code ?? $this->defaults->country()?->alpha_code_2;
    }

    /**
     * Prefiks telefonu kraju (`+48`), null dla kraju bez prefiksu w słowniku.
     */
    public function dialCode(?string $code): ?string
    {
        $dial = $code !== null ? $this->countries()->firstWhere('alpha_code_2', $code)?->dial_code : null;

        return $dial !== null && $dial !== '' ? '+' . ltrim($dial, '+') : null;
    }

    /**
     * Telefon do zapisu: numer z prefiksem kraju; numer wpisany już z `+` zostaje bez zmian.
     */
    public function compose(?string $code, string $number): string
    {
        $dial = $this->dialCode($code);

        return str_starts_with($number, '+') || $dial === null ? $number : $dial . ' ' . $number;
    }

    /**
     * Zapisany telefon rozbity do formularza: kraj prefiksu (najdłuższy pasujący prefiks; przy kilku krajach
     * z tym prefiksem — $preferred) i numer bez prefiksu; numer bez `+` dostaje kraj $preferred.
     *
     * @return array{country: ?string, number: ?string}
     */
    public function split(?string $phone, ?string $preferred): array
    {
        if ($phone === null || $phone === '' || ! str_starts_with($phone, '+')) {
            return ['country' => $preferred, 'number' => $phone];
        }

        $match = null;

        foreach ($this->countries() as $country) {
            $dial = $this->dialCode((string) $country->alpha_code_2);

            if ($dial === null || ! str_starts_with($phone, $dial) || ($match !== null && strlen($dial) < strlen($match['dial']))) {
                continue;
            }

            if ($match === null || strlen($dial) > strlen($match['dial']) || $country->alpha_code_2 === $preferred) {
                $match = ['code' => (string) $country->alpha_code_2, 'dial' => $dial];
            }
        }

        return $match === null
            ? ['country' => $preferred, 'number' => $phone]
            : ['country' => $match['code'], 'number' => ltrim(substr($phone, strlen($match['dial'])))];
    }
}
