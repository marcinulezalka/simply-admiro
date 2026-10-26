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

namespace Simplysmart\Admiro\Audit\Events;

use Simplysmart\Admiro\Auth\AccountActor;
use Simplysmart\Admiro\Enums\RecordStatus;

/**
 * Stan wiersza (RecordStatus) zmieniony przez Database\RecordStatusTransitions: tabela i token wiersza, stan przed
 * i po, opcjonalnie powód i konto, które zmieniło stan (null — proces aplikacji, np. migracja).
 */
final readonly class RecordStatusChanged
{
    public function __construct(
        public string $table,
        public string $token,
        public ?RecordStatus $from,
        public RecordStatus $to,
        public ?string $reason = null,
        public ?AccountActor $actor = null,
    ) {}
}
