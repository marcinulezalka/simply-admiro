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
use Simplysmart\Admiro\Http\Controllers\CookieController;
use Simplysmart\Admiro\Http\Controllers\LegalDocumentController;
use Simplysmart\Admiro\Providers\CookiesServiceProvider;

/*
 * Trasy Core (nazwy z prefiksem `admiro.`, prefiks {locale} i middleware z Providers\CoreRoutesServiceProvider).
 * Strefa administratorów ma ten sam prefiks nazw: jej trasy nie mogą się nazywać `cookies.*` ani `legal.*`.
 */
Route::get('cookies', [CookieController::class, 'policy'])->name('cookies.policy');
Route::get('polityka-prywatnosci', [LegalDocumentController::class, 'privacy'])->name('legal.privacy');
Route::get('rodo', [LegalDocumentController::class, 'gdpr'])->name('legal.gdpr');
Route::post('cookies/consent', [CookieController::class, 'consent'])->middleware('throttle:' . CookiesServiceProvider::THROTTLE)->name('cookies.consent');
