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
use Simplysmart\Admiro\Enums\Permission;
use Simplysmart\Admiro\Http\AccountAction;
use Simplysmart\Admiro\Http\Controllers\AccountActionController;
use Simplysmart\Admiro\Http\Controllers\AdministratorController;
use Simplysmart\Admiro\Http\Controllers\OrganizationController;
use Simplysmart\Admiro\Http\Controllers\OwnerController;
use Simplysmart\Admiro\Http\Controllers\ZoneController;
use Simplysmart\Mainboard\Zones\ZonePermission;

/*
| Sekcje panelu z uprawnieniem (poziom Authenticated: Core dodaje auth:<guard>; uprawnienie sprawdza
| zone.permission w organizacji operatora). Ścieżki względne, nazwy krótkie: prefiks "admiro." daje provider.
*/

Route::middleware(ZonePermission::middleware(Permission::ZonesView))->group(static function (): void {
    Route::get('zones', [ZoneController::class, 'index'])->name('zones.index');
    Route::get('zones/{managedZone}/accounts', [ZoneController::class, 'accounts'])->name('zones.accounts');
});

Route::post('zones/{managedZone}/accounts/{account}/{action}', AccountActionController::class)
    ->whereIn('action', array_column(AccountAction::cases(), 'value'))
    ->middleware([ZonePermission::middleware(Permission::AccountsManage), 'throttle:mainboard.sign-in'])
    ->name('zones.accounts.action');

Route::middleware(ZonePermission::middleware(Permission::OwnersAssign))->group(static function (): void {
    Route::get('owners/create', [OwnerController::class, 'create'])->name('owners.create');
    Route::post('owners', [OwnerController::class, 'store'])->middleware('throttle:mainboard.sign-in')->name('owners.store');
});

Route::middleware(ZonePermission::middleware(Permission::UsersManage))->group(static function (): void {
    Route::get('administrators', [AdministratorController::class, 'index'])->name('administrators.index');
    Route::post('administrators/{account}/roles', [AdministratorController::class, 'assign'])->middleware('throttle:mainboard.sign-in')->name('administrators.roles.assign');
    Route::delete('administrators/{account}/roles/{role}', [AdministratorController::class, 'revoke'])->middleware('throttle:mainboard.sign-in')->name('administrators.roles.revoke');
});

Route::middleware(ZonePermission::middleware(Permission::OrganizationsManage))->group(static function (): void {
    Route::get('organizations/create', [OrganizationController::class, 'create'])->name('organizations.create');
    Route::post('organizations', [OrganizationController::class, 'store'])->middleware('throttle:mainboard.sign-in')->name('organizations.store');
    Route::get('organizations/{organization}/edit', [OrganizationController::class, 'edit'])->name('organizations.edit');
    Route::put('organizations/{organization}', [OrganizationController::class, 'update'])->middleware('throttle:mainboard.sign-in')->name('organizations.update');
    Route::post('organizations/{organization}/toggle', [OrganizationController::class, 'toggle'])->middleware('throttle:mainboard.sign-in')->name('organizations.toggle');
    Route::delete('organizations/{organization}/members/{managedZone}/{account}', [OrganizationController::class, 'removeMember'])->middleware('throttle:mainboard.sign-in')->name('organizations.members.remove');
});

Route::middleware(ZonePermission::middleware(Permission::OrganizationsView))->group(static function (): void {
    Route::get('organizations', [OrganizationController::class, 'index'])->name('organizations.index');
    Route::get('organizations/{organization}/members', [OrganizationController::class, 'members'])->name('organizations.members');
});
