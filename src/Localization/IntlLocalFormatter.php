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

namespace Simplysmart\Mainboard\Localization;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Support\Number;
use InvalidArgumentException;
use RuntimeException;
use Simplysmart\Mainboard\Contracts\LocalFormatter;

/**
 * Formatowanie regionalne przez intl (Number) i Carbon; bieżący język bierze z translatora,
 * a domyślną walutę z config('mainboard.locale.currency').
 */
final readonly class IntlLocalFormatter implements LocalFormatter
{
    private const string DATE_FORMAT = 'L';

    private const string DAY_FORMAT = 'dddd';

    private const string MONTH_FORMAT = 'MMMM';

    private const int DAYS_IN_WEEK = 7;

    private const int MONTHS_IN_YEAR = 12;

    /**
     * Poniedziałek odniesienia: dzień ISO n to ta data plus n - 1 dni.
     */
    private const string REFERENCE_MONDAY = '2024-01-01';

    private const string CURRENCY_CONFIG = 'mainboard.locale.currency';

    public function __construct(
        private Translator $translator,
        private Repository $config,
    ) {}

    public function money(int|float $amount, ?string $currency = null, ?string $locale = null): string
    {
        return $this->formatted(Number::currency($amount, $currency ?? $this->defaultCurrency(), $this->locale($locale)));
    }

    public function number(int|float $number, int $precision = 0, ?string $locale = null): string
    {
        return $this->formatted(Number::format($number, $precision, null, $this->locale($locale)));
    }

    public function date(DateTimeInterface|string $date, ?string $format = null, ?string $locale = null): string
    {
        return $this->localized(new CarbonImmutable($date), $locale)->isoFormat($format ?? self::DATE_FORMAT);
    }

    public function day(int|DateTimeInterface $day, ?string $format = null, ?string $locale = null): string
    {
        $date = is_int($day)
            ? new CarbonImmutable(self::REFERENCE_MONDAY)->addDays($this->inRange($day, self::DAYS_IN_WEEK) - 1)
            : new CarbonImmutable($day);

        return $this->localized($date, $locale)->isoFormat($format ?? self::DAY_FORMAT);
    }

    public function month(int|DateTimeInterface $month, ?string $format = null, ?string $locale = null): string
    {
        $date = is_int($month)
            ? new CarbonImmutable(self::REFERENCE_MONDAY)->month($this->inRange($month, self::MONTHS_IN_YEAR))
            : new CarbonImmutable($month);

        return $this->localized($date, $locale)->isoFormat($format ?? self::MONTH_FORMAT);
    }

    /**
     * @throws RuntimeException gdy intl nie sformatował wartości (np. nieznany język lub waluta)
     */
    private function formatted(string|false $result): string
    {
        if ($result === false) {
            throw new RuntimeException('Formatowanie intl nie powiodło się.');
        }

        return $result;
    }

    /**
     * @throws InvalidArgumentException gdy w konfiguracji waluta nie jest tekstem
     */
    private function defaultCurrency(): string
    {
        $currency = $this->config->get(self::CURRENCY_CONFIG);

        if (! is_string($currency) || $currency === '') {
            throw new InvalidArgumentException('Ustaw walutę w ' . self::CURRENCY_CONFIG . ' (APP_CURRENCY).');
        }

        return $currency;
    }

    private function localized(CarbonImmutable $date, ?string $locale): CarbonImmutable
    {
        return $date->settings(['locale' => $this->locale($locale)]);
    }

    private function locale(?string $locale): string
    {
        return $locale ?? $this->translator->getLocale();
    }

    /**
     * @throws InvalidArgumentException gdy numer jest poza zakresem 1..$max
     */
    private function inRange(int $value, int $max): int
    {
        if ($value < 1 || $value > $max) {
            throw new InvalidArgumentException("Oczekiwano liczby od 1 do {$max}, podano {$value}.");
        }

        return $value;
    }
}
