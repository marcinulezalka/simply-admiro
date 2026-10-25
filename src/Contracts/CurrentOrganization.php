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

namespace Simplysmart\Admiro\Contracts;

use Closure;

/**
 * Organizacja, w której działa bieżące żądanie: zakres ról, uprawnień i odmów konta (zespół spatie).
 * Ustawia ją middleware UseCurrentTeam; null = brak organizacji, czyli brak uprawnień (fail closed).
 */
interface CurrentOrganization
{
    public function id(): ?int;

    public function set(?int $organizationId): void;

    /**
     * Wykonuje kod w podanej organizacji i przywraca poprzednią, także po wyjątku.
     *
     * @template TResult
     *
     * @param Closure(): TResult $callback
     *
     * @return TResult
     */
    public function within(?int $organizationId, Closure $callback): mixed;
}
