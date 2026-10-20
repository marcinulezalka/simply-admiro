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

namespace Simplysmart\Mainboard\Zones;

/**
 * Ustawienia sesji strefy; null oznacza wartość z config/session.php aplikacji.
 */
final readonly class ZoneSessionSettings
{
    public function __construct(
        public string $cookie,
        public ?string $table,
        public ?int $lifetime,
        public ?bool $expireOnClose,
        public ?string $sameSite,
        public ?bool $encrypt,
    ) {}

    /**
     * Klucze config/session.php, które strefa nadpisuje.
     *
     * @return array<string, string|int|bool>
     */
    public function toSessionConfig(): array
    {
        return array_filter([
            'cookie' => $this->cookie,
            'table' => $this->table,
            'lifetime' => $this->lifetime,
            'expire_on_close' => $this->expireOnClose,
            'same_site' => $this->sameSite,
            'encrypt' => $this->encrypt,
        ], static fn(string|int|bool|null $value): bool => $value !== null);
    }
}
