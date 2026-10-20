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

namespace Simplysmart\Mainboard\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Odrzuca PIN-y łatwe do zgadnięcia: same powtórzenia (1111) i kolejne cyfry rosnąco
 * lub malejąco, także z przejściem przez zero (8901, 2109).
 */
final class StrongPin implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! ctype_digit($value) || strlen($value) < 2) {
            return;
        }

        if ($this->isRepeated($value) || $this->isSequence($value, 1) || $this->isSequence($value, -1)) {
            $fail(text('sign_in.weak_pin'));
        }
    }

    private function isRepeated(string $pin): bool
    {
        return count(array_unique(str_split($pin))) === 1;
    }

    private function isSequence(string $pin, int $step): bool
    {
        $digits = array_map(intval(...), str_split($pin));

        for ($i = 1, $count = count($digits); $i < $count; $i++) {
            if ($digits[$i] !== ($digits[$i - 1] + $step + 10) % 10) {
                return false;
            }
        }

        return true;
    }
}
