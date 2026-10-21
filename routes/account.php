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

/*
| Trasy dla zalogowanych administratorów (poziom Authenticated: Core dodaje auth:<guard>).
| Ścieżki względne, nazwy krótkie: prefiks URL, prefiks nazw "admiro." i middleware daje provider.
*/

Route::view('/', 'admiro::dashboard')->name('dashboard');
