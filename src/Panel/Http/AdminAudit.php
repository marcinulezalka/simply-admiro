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

namespace Simplysmart\Admiro\Panel\Http;

use Illuminate\Contracts\Events\Dispatcher;
use Simplysmart\Mainboard\Audit\Events\AdminActionPerformed;

/**
 * Akcja administratora w dzienniku zdarzeń Core (plik i tabela), zawsze z aktorem.
 */
final readonly class AdminAudit
{
    public function __construct(
        private Dispatcher $events,
        private AdminContext $context,
    ) {}

    /**
     * @param array<string, scalar|null> $context Szczegóły bez danych osobowych.
     */
    public function record(string $action, ?string $zone = null, int|string|null $accountId = null, ?int $organizationId = null, array $context = []): void
    {
        $this->events->dispatch(new AdminActionPerformed($action, $this->context->actor(), $zone, $accountId, $organizationId, $context));
    }
}
