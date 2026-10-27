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
use Simplysmart\Admiro\Panel\Enums\OnboardingStep;
use Simplysmart\Admiro\Panel\Enums\Permission;
use Simplysmart\Admiro\Panel\Http\Controllers\DashboardController;
use Simplysmart\Admiro\Panel\Http\Controllers\OnboardingController;
use Simplysmart\Admiro\Panel\Http\Controllers\SearchController;
use Simplysmart\Admiro\Zones\ZonePermission;

/*
| Trasy dla zalogowanych administratorów (poziom Authenticated: Core dodaje auth:<guard>).
| Ścieżki względne, nazwy krótkie: prefiks URL, prefiks nazw "admiro." i middleware daje provider.
*/

Route::get('/', DashboardController::class)->name('dashboard');
Route::view('favorites', 'admiro::panel.favorites')->name('favorites');
Route::view('board', 'admiro::panel.board')->name('board');

Route::get('search', [SearchController::class, 'index'])->name('search');
Route::get('search/suggest', [SearchController::class, 'suggest'])->middleware('throttle:60,1')->name('search.suggest');

Route::get('onboarding', [OnboardingController::class, 'index'])->name('onboarding.index');
Route::get('onboarding/{step}', [OnboardingController::class, 'show'])->whereIn('step', array_column(OnboardingStep::cases(), 'value'))->name('onboarding.show');
Route::post('onboarding/{step}/confirm', [OnboardingController::class, 'confirm'])
    ->whereIn('step', array_column(OnboardingStep::cases(), 'value'))
    ->middleware([ZonePermission::middleware(Permission::SettingsManage), 'throttle:admiro.sign-in'])
    ->name('onboarding.confirm');
