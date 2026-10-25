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

namespace Simplysmart\Admiro\Enums;

use Simplysmart\Admiro\Contracts\HasLabel;

/**
 * Rodzaje stawek VAT. Kraj może mieć kilka stawek obniżonych naraz (w Polsce 8% i 5%);
 * stawka zwolniona („zw”) nie ma procentu.
 */
enum TaxRateType: string implements HasLabel
{
    case Standard = 'standard';
    case Reduced = 'reduced';
    case SuperReduced = 'super-reduced';
    case Zero = 'zero';
    case Exempt = 'exempt';

    /**
     * Rodzaj stawki niższej od podstawowej według procentu: 0 — zerowa, poniżej 5% — super obniżona,
     * w pozostałych przypadkach obniżona (podział stosowany w UE).
     */
    public static function forReducedPercentage(float $rate): self
    {
        return match (true) {
            $rate == 0.0 => self::Zero,
            $rate < 5 => self::SuperReduced,
            default => self::Reduced,
        };
    }

    public function label(): string
    {
        return text('enums.tax_rate_type.' . $this->value);
    }

    /**
     * Czy stawka tego rodzaju ma procent.
     */
    public function hasPercentage(): bool
    {
        return $this !== self::Exempt;
    }

    /**
     * Tylko stawka podstawowa jest jedna na kraj i dzień (nowa kończy poprzednią); inne mogą
     * obowiązywać obok siebie.
     */
    public function isUniquePerCountry(): bool
    {
        return $this === self::Standard;
    }
}
