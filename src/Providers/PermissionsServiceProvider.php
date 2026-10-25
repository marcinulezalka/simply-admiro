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

namespace Simplysmart\Admiro\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Simplysmart\Admiro\Console\SeedOrganizations;
use Simplysmart\Admiro\Console\SyncZonePermissions;
use Simplysmart\Admiro\Contracts\CurrentOrganization;
use Simplysmart\Admiro\Contracts\CurrentRole;
use Simplysmart\Admiro\Contracts\ZonePermissionChecker;
use Simplysmart\Admiro\Http\Middleware\RequireZonePermission;
use Simplysmart\Admiro\Permissions\PermissionDefaults;
use Simplysmart\Admiro\Permissions\PermissionFiles;
use Simplysmart\Admiro\Permissions\RequestRole;
use Simplysmart\Admiro\Permissions\SpatieCurrentOrganization;
use Simplysmart\Admiro\Permissions\SpatiePermissionChecker;
use Simplysmart\Admiro\Permissions\ZoneOwnerProvisioners;

/**
 * Organizacje i uprawnienia stref na spatie/laravel-permission: kontrakty sprawdzania, bieżącej organizacji i aktywnej roli,
 * rejestry plików ról (z katalogiem Core) i zakładania właścicieli, middleware `zone.permission`, komendy
 * organizacji i ról oraz konfiguracja spatie ustawiana po register() wszystkich providerów (wtedy config spatie
 * jest już scalony), przed ich boot().
 */
final class PermissionsServiceProvider extends ServiceProvider
{
    private const string CORE_PERMISSION_FILES = __DIR__ . '/../../database/seeders/permissions';

    public function register(): void
    {
        $this->app->bind(ZonePermissionChecker::class, SpatiePermissionChecker::class);
        $this->app->bind(CurrentOrganization::class, SpatieCurrentOrganization::class);
        $this->app->scoped(CurrentRole::class, RequestRole::class);
        $this->app->singleton(ZoneOwnerProvisioners::class);
        $this->app->singleton(PermissionFiles::class, static function (): PermissionFiles {
            $files = new PermissionFiles();
            $files->add(self::CORE_PERMISSION_FILES);

            return $files;
        });
        $this->app->booting(function (): void {
            $this->app->make(PermissionDefaults::class)->apply();
        });
    }

    public function boot(Router $router): void
    {
        $router->aliasMiddleware(RequireZonePermission::ALIAS, RequireZonePermission::class);

        if ($this->app->runningInConsole()) {
            $this->commands([SeedOrganizations::class, SyncZonePermissions::class]);
        }
    }
}
