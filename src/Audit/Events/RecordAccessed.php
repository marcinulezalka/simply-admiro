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

namespace Simplysmart\Mainboard\Audit\Events;

use Simplysmart\Mainboard\Auth\AccountActor;

/**
 * Konto otworzyło rekord z danymi osobowymi (np. kartotekę), opcjonalnie z danymi wrażliwymi (medycznymi):
 * trafia do dziennika zdarzeń organizacji, która jest właścicielem rekordu (dziennik dostępu). Rekord jest wskazany
 * typem i tokenem, bez treści; `viewerOrganizationId` to organizacja oglądającego, gdy jest inna niż właściciel.
 */
final readonly class RecordAccessed
{
    public function __construct(
        public string $zone,
        public AccountActor $actor,
        public int $organizationId,
        public string $recordType,
        public string $recordToken,
        public bool $sensitive = false,
        public ?int $viewerOrganizationId = null,
    ) {}
}
