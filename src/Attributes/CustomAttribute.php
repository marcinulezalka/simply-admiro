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

namespace Simplysmart\Admiro\Attributes;

/**
 * Atrybut dodany przez użytkownika do rekordu danych (Attributes\CustomAttributes): grupa (opcjonalna), nazwa
 * i wartość, w kolejności ustawionej przeciąganiem.
 */
final readonly class CustomAttribute
{
    public function __construct(
        public ?string $group,
        public string $name,
        public ?string $value,
    ) {}

    /**
     * @return array{group: ?string, name: string, value: ?string}
     */
    public function toArray(): array
    {
        return ['group' => $this->group, 'name' => $this->name, 'value' => $this->value];
    }
}
