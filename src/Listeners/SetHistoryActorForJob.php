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

namespace Simplysmart\Mainboard\Listeners;

use Illuminate\Queue\Events\JobProcessing;
use Simplysmart\Mainboard\Database\HistoryActor;

/**
 * Zadanie kolejki zmienia dane jako „system” (bez strefy i konta), z identyfikatorem zadania
 * w miejscu identyfikatora żądania; po zadaniu aktor jest czyszczony.
 */
final readonly class SetHistoryActorForJob
{
    public function __construct(
        private HistoryActor $actor,
    ) {}

    public function start(JobProcessing $event): void
    {
        $this->actor->set(null, null, $event->job->uuid());
    }

    public function finish(): void
    {
        $this->actor->clear();
    }
}
