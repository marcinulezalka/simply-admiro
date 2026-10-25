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

namespace Simplysmart\Admiro\Tools\Release;

use DateTimeImmutable;
use DateTimeZone;
use Exception;

/**
 * Źródło momentu wydania w strefie czasowej wydań.
 */
final readonly class ReleaseClock
{
    public const string TIMEZONE_ENV = 'RELEASE_TIMEZONE';

    public const string DEFAULT_TIMEZONE = 'Europe/Warsaw';

    /**
     * @param  string  $timezone  Nazwa strefy czasowej, np. „Europe/Warsaw”.
     */
    public function __construct(
        private string $timezone = self::DEFAULT_TIMEZONE,
    ) {}

    /**
     * Tworzy zegar w strefie ze zmiennej środowiskowej RELEASE_TIMEZONE albo domyślnej.
     */
    public static function fromEnvironment(): self
    {
        return new self(getenv(self::TIMEZONE_ENV) ?: self::DEFAULT_TIMEZONE);
    }

    /**
     * Zwraca bieżący moment w strefie czasowej zegara.
     *
     * @throws ReleaseException Gdy strefa czasowa jest nieznana.
     */
    public function now(): DateTimeImmutable
    {
        try {
            return new DateTimeImmutable('now', new DateTimeZone($this->timezone));
        } catch (Exception) {
            throw new ReleaseException('Nieznana strefa czasowa w ' . self::TIMEZONE_ENV . ": „{$this->timezone}”.");
        }
    }
}
