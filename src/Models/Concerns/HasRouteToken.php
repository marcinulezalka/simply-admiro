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

namespace Simplysmart\Mainboard\Models\Concerns;

use Illuminate\Support\Str;
use Simplysmart\Mainboard\Auth\AccountColumns;

/**
 * Losowy `token` (64 znaki) jako klucz trasy: publiczne adresy nigdy nie zawierają sekwencyjnego `id`.
 */
trait HasRouteToken
{
    public const int TOKEN_LENGTH = 64;

    public static function bootHasRouteToken(): void
    {
        static::creating(static function (self $model): void {
            if (! is_string($model->getAttribute(AccountColumns::TOKEN)) || $model->getAttribute(AccountColumns::TOKEN) === '') {
                $model->setAttribute(AccountColumns::TOKEN, Str::random(self::TOKEN_LENGTH));
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return AccountColumns::TOKEN;
    }
}
