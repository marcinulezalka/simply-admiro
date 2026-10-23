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

use Illuminate\Support\Facades\Route;
use Simplysmart\Mainboard\Http\Controllers\DesignSystemController;

/*
 * Trasy Core (nazwy z prefiksem `mainboard.`, prefiks {locale} i middleware z Providers\CoreRoutesServiceProvider).
 */
Route::get('design-system', [DesignSystemController::class, 'index'])->name('design-system');
Route::get('design-system/views/{view}', [DesignSystemController::class, 'show'])->name('design-system.view');
