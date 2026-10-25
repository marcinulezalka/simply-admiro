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

use Illuminate\Support\Facades\Route;
use Simplysmart\Admiro\Http\Controllers\DesignSystemController;

/*
 * Strona design systemu (nazwy z prefiksem `simplysmart.`, prefiks {locale} i middleware z
 * Providers\CoreRoutesServiceProvider; włączana per żądanie przez DESIGN_SYSTEM_PAGE).
 */
Route::get('design-system', [DesignSystemController::class, 'index'])->name('design-system');
Route::get('design-system/views/{view}', [DesignSystemController::class, 'show'])->name('design-system.view');
