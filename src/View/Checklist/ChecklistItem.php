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

namespace Simplysmart\Admiro\View\Checklist;

/**
 * Jedno zadanie listy zadań (x-admiro::checklist.progress i x-admiro::checklist.steps, np. onboarding panelu albo
 * konfiguracja organizacji w strefie): numer, nazwa, opis, ikona, odnośnik, czy zrobione, etykieta nad nazwą
 * (np. „Zadanie 2 · W trakcie”) oraz stan z tonem plakietki (`.ss-badge--<ton>`) w liście postępu.
 */
final readonly class ChecklistItem
{
    public function __construct(
        public int $number,
        public string $title,
        public string $lead,
        public string $icon,
        public string $url,
        public bool $done,
        public string $eyebrow,
        public string $state,
        public string $tone,
    ) {}
}
