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

namespace Simplysmart\Mainboard\Auth\Devices;

/**
 * Token zaufanego urządzenia z ciasteczka: `selector` (jawny, wyszukuje urządzenie) i `validator`
 * (tajny, w bazie tylko jego HMAC).
 */
final readonly class DeviceToken
{
    private const string SEPARATOR = '.';

    private const string PATTERN = '/^[0-9a-f]{32}\.[0-9a-f]{64}$/';

    public function __construct(
        public string $selector,
        #[\SensitiveParameter]
        public string $validator,
    ) {}

    /**
     * Token z wartości ciasteczka; wartość w złym formacie to brak tokenu.
     */
    public static function fromCookie(mixed $value): ?self
    {
        if (! is_string($value) || preg_match(self::PATTERN, $value) !== 1) {
            return null;
        }

        [$selector, $validator] = explode(self::SEPARATOR, $value, 2);

        return new self($selector, $validator);
    }

    public function toCookie(): string
    {
        return $this->selector . self::SEPARATOR . $this->validator;
    }
}
