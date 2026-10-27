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

namespace Simplysmart\Admiro\Panel;

use Illuminate\Contracts\Events\Dispatcher;
use Simplysmart\Admiro\Audit\Events\AdminActionPerformed;
use Simplysmart\Admiro\Mail\MailBrandGroups;
use Simplysmart\Admiro\Mail\MailBrandResolver;
use Simplysmart\Admiro\Panel\Console\CreateAdmiroAccount;
use Simplysmart\Admiro\Panel\Onboarding\RecordOnboardingProgress;
use Simplysmart\Admiro\Permissions\Events\ZoneRoleChanged;
use Simplysmart\Admiro\Contracts\ZoneRegistry;
use Simplysmart\Admiro\Zones\ZoneDefinitionFactory;
use Simplysmart\Admiro\Zones\ZoneGuardRegistrar;
use Simplysmart\Admiro\Zones\ZoneMiddlewareRegistrar;
use Simplysmart\Admiro\Zones\ZoneRouteRegistrar;
use Simplysmart\Admiro\Zones\ZoneServiceProvider;
use Simplysmart\Admiro\Panel\Http\AdmiroNavigation;
use Simplysmart\Admiro\Translation\CascadeTextTranslator;
use Simplysmart\Admiro\Zones\ZoneNavigations;

/**
 * Strefa administratorów na mechanizmie stref: rejestracja strefy (config `admiro.zone`), trasy, menu, grupa logotypów e-maili, zapis postępu
 * onboardingu (RecordOnboardingProgress) i komenda zakładania kont. Config, widoki (`admiro::panel.*`) i teksty (`admiro::...`) ładuje provider rdzenia.
 */
final class PanelServiceProvider extends ZoneServiceProvider
{
    /**
     * Namespace tekstów strefy (`text('admiro::...')`, `sign_in.admiro.*`): wspólny z rdzeniem.
     */
    public const string NAMESPACE = CascadeTextTranslator::CORE_NAMESPACE;

    /**
     * Grupa logotypów w e-mailach strefy (MailBrandGroups): simplysmart na pozycji 10, admiro na 30; pozycja 20
     * zostaje dla produktu, który strefa obsługuje (dopisuje go paczka produktu w swoim providerze).
     */
    public const string MAIL_BRAND_CORE = 'simplysmart';

    public const int MAIL_BRAND_CORE_POSITION = 10;

    public const string MAIL_BRAND_ZONE = 'admiro';

    public const int MAIL_BRAND_ZONE_POSITION = 30;

    /**
     * Pasek nagłówka e-maili strefy: grafitowy gradient z koroną wyciętą z lewego dolnego rogu (motyw sygnetu)
     * i białym logotypem admiro po prawej, wyśrodkowanym w pionie.
     */
    public const string MAIL_HEADER = __DIR__ . '/../../public/images/mail-header.png';

    public function boot(
        ZoneDefinitionFactory $factory,
        ZoneRegistry $zones,
        ZoneGuardRegistrar $guards,
        ZoneMiddlewareRegistrar $middleware,
        ZoneRouteRegistrar $routes,
    ): void {
        parent::boot($factory, $zones, $guards, $middleware, $routes);

        $this->app->make(ZoneNavigations::class)->add(config()->string('admiro.zone.name'), AdmiroNavigation::class);
        $this->app->make(MailBrandGroups::class)->add(config()->string('admiro.zone.name'), self::MAIL_BRAND_CORE, MailBrandResolver::CORE_LOGO, self::MAIL_BRAND_CORE_POSITION);
        $this->app->make(MailBrandGroups::class)->add(config()->string('admiro.zone.name'), self::MAIL_BRAND_ZONE, config()->string('admiro.zone.brand.logo'), self::MAIL_BRAND_ZONE_POSITION);
        $this->app->make(Dispatcher::class)->listen([AdminActionPerformed::class, ZoneRoleChanged::class], RecordOnboardingProgress::class);

        if ($this->app->runningInConsole()) {
            $this->commands([CreateAdmiroAccount::class]);
        }
    }

    protected function routeNamePrefix(): string
    {
        return 'admiro';
    }

    protected function zoneConfig(): array
    {
        return config()->array('admiro.zone');
    }

    protected function routesPath(): string
    {
        return __DIR__ . '/../../routes/panel';
    }
}
