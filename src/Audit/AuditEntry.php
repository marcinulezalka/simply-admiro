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

namespace Simplysmart\Admiro\Audit;

use Simplysmart\Admiro\Auth\AccountActor;

/**
 * Wpis dziennika zdarzeń: co się stało, w której strefie, którego konta dotyczy, kto to zrobił (null = konto
 * samo albo system) i w której organizacji. Bez adresów e-mail; IP osobno (w bazie szyfrowane).
 */
final readonly class AuditEntry
{
    /**
     * @param array<string, scalar|null> $context Dodatkowe dane zdarzenia (bez danych osobowych).
     */
    public function __construct(
        public string $event,
        public AuditLevel $level,
        public ?string $zone,
        public int|string|null $accountId,
        public ?AccountActor $actor = null,
        public ?int $organizationId = null,
        public ?string $ip = null,
        public array $context = [],
    ) {}

    /**
     * Kontekst wpisu w pliku logu.
     *
     * @return array<string, mixed>
     */
    public function toLogContext(): array
    {
        return array_filter([
            'zone' => $this->zone,
            'account_id' => $this->accountId,
            'organization_id' => $this->organizationId,
            'actor' => $this->actor?->toLogContext(),
            'ip' => $this->ip,
            ...$this->context,
        ], static fn(mixed $value): bool => $value !== null);
    }
}
