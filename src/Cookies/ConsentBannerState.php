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

namespace Simplysmart\Admiro\Cookies;

/**
 * Dane banera cookies: wersja polityki (0 bez polityki), cookies według kategorii, skrypty opcjonalne, nazwa
 * ciasteczka zgody, czas jego ważności w dniach, adres zapisu zgody i adres strony polityki (null bez polityki).
 */
final readonly class ConsentBannerState
{
    /**
     * @param array<string, list<CookieDescription>> $cookies Kategoria → cookies.
     * @param list<ConsentScript> $scripts
     */
    public function __construct(
        public int $version,
        public array $cookies,
        public array $scripts,
        public string $cookieName,
        public int $lifetimeDays,
        public string $consentUrl,
        public ?string $policyUrl,
    ) {}
}
