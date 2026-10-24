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

namespace Simplysmart\Mainboard\Contracts;

use Carbon\CarbonImmutable;
use Simplysmart\Mainboard\ExchangeRates\ExchangeRateQuote;
use Simplysmart\Mainboard\ExchangeRates\ExchangeRatesUnavailable;

/**
 * Źródło kursów walut w złotych (domyślnie tabela A NBP, `ExchangeRates\NbpTableA`): kursy opublikowane w dniach
 * od–do włącznie. Dni bez publikacji (weekendy, święta) po prostu nie mają kursów.
 */
interface ExchangeRateProvider
{
    /**
     * @return list<ExchangeRateQuote>
     *
     * @throws ExchangeRatesUnavailable
     */
    public function between(CarbonImmutable $from, CarbonImmutable $to): array;
}
