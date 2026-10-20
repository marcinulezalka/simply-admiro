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

namespace Simplysmart\Mainboard\Models;

use Illuminate\Database\Eloquent\Model;
use Simplysmart\Mainboard\Models\Concerns\HasRouteToken;

/**
 * Baza modeli Core i stref (docs/baza-danych.md §5): klucz trasy to losowy `token`.
 */
abstract class MainModel extends Model
{
    use HasRouteToken;
}
