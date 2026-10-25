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

namespace Simplysmart\Mainboard\View\Demo;

/**
 * Przykładowa faktura widoków design systemu (dane do pokazania układu, nie z bazy).
 */
final readonly class DemoInvoice
{
    public function __construct(
        public string $number,
        public string $client,
        public string $issued,
        public string $due,
        public float $net,
        public int $vatRate,
        public DemoInvoiceStatus $status,
    ) {}

    public function vat(): float
    {
        return round($this->net * $this->vatRate / 100, 2);
    }

    public function gross(): float
    {
        return $this->net + $this->vat();
    }
}
