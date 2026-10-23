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

namespace Simplysmart\Admiro\Http;

use Illuminate\Config\Repository;
use Illuminate\Log\LogManager;

/**
 * Wpis akcji administratora w dzienniku logowań Core (`mainboard.auth.log_channel`), zawsze z aktorem.
 */
final readonly class AdminAudit
{
    public function __construct(
        private LogManager $logs,
        private Repository $config,
        private AdminContext $context,
    ) {}

    /**
     * @param array<string, mixed> $context
     */
    public function record(string $event, array $context): void
    {
        $this->logs->channel($this->config->string('mainboard.auth.log_channel'))
            ->notice($event, [...$context, 'actor' => $this->context->actor()->toLogContext()]);
    }
}
