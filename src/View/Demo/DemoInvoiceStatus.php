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

namespace Simplysmart\Admiro\View\Demo;

/**
 * Status przykładowej faktury na stronie design systemu; przypadki w kolejności procesu to etapy steppera
 * (docs/design-system.md §5: etapy steppera to przypadki enuma `status` rekordu).
 */
enum DemoInvoiceStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Accepted = 'accepted';
    case Paid = 'paid';
    case Closed = 'closed';

    public function label(): string
    {
        return text('design_system.demo.statuses.' . $this->value);
    }

    /**
     * Pozycja etapu w procesie (od zera), do porównania z bieżącym etapem w stepperze.
     */
    public function position(): int
    {
        return (int) array_search($this, self::cases(), true);
    }
}
