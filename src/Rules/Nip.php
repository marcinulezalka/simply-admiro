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

namespace Simplysmart\Admiro\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Numer NIP: 10 cyfr (spacje, myślniki i prefiks kraju pomijane) z poprawną cyfrą kontrolną
 * (wagi 6, 5, 7, 2, 3, 4, 5, 6, 7). Zapisuje się go w postaci z normalize().
 */
final class Nip implements ValidationRule
{
    private const array WEIGHTS = [6, 5, 7, 2, 3, 4, 5, 6, 7];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! self::isValid(self::normalize($value))) {
            $fail(text('validation.nip'));
        }
    }

    /**
     * NIP bez spacji, myślników i prefiksu kraju („PL”): same cyfry.
     */
    public static function normalize(string $nip): string
    {
        return (string) preg_replace('/\D+/', '', $nip);
    }

    public static function isValid(string $nip): bool
    {
        if (preg_match('/^\d{10}$/', $nip) !== 1) {
            return false;
        }

        $sum = 0;

        foreach (self::WEIGHTS as $index => $weight) {
            $sum += (int) $nip[$index] * $weight;
        }

        return $sum % 11 !== 10 && $sum % 11 === (int) $nip[9];
    }
}
