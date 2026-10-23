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

namespace Simplysmart\Mainboard\Providers;

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use LogicException;
use Simplysmart\Mainboard\Contracts\CurrentAccount;
use Simplysmart\Mainboard\Contracts\CurrentOrganization;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Contracts\ZoneRegistry;
use Simplysmart\Mainboard\Enums\AuthLayout;
use Simplysmart\Mainboard\Queries\Organizations\ActiveMemberships;
use Simplysmart\Mainboard\Translation\CascadeTextTranslator;
use Simplysmart\Mainboard\Zones\InMemoryZoneRegistry;
use Simplysmart\Mainboard\Zones\RequestAccount;
use Simplysmart\Mainboard\Zones\RequestZone;
use Simplysmart\Mainboard\Zones\ZoneRedirects;
use Simplysmart\Mainboard\Zones\ZoneSessionConfigurator;
use Simplysmart\Mainboard\Zones\ZoneSessionManager;

/**
 * Mechanizm stref: rejestr (singleton), strefa i konto żądania, osobny magazyn sesji każdej strefy,
 * przekierowania auth/guest według strefy oraz dane stref dla widoków (layout strefy, logowanie i jego układ, konto).
 */
final class ZonesServiceProvider extends ServiceProvider
{
    private const string SIGN_IN_VIEWS = CascadeTextTranslator::CORE_NAMESPACE . '::auth.*';

    private const string ACCOUNT_VIEWS = CascadeTextTranslator::CORE_NAMESPACE . '::account.*';

    private const string ZONE_LAYOUT_VIEW = CascadeTextTranslator::CORE_NAMESPACE . '::components.zone.layout';

    private const string AUTH_LAYOUT_VIEW = CascadeTextTranslator::CORE_NAMESPACE . '::components.auth.layout';

    public function register(): void
    {
        $this->app->singleton(ZoneRegistry::class, InMemoryZoneRegistry::class);
        $this->app->scoped(CurrentZone::class, RequestZone::class);
        $this->app->bind(CurrentAccount::class, RequestAccount::class);

        $this->app->extend('session', fn(): ZoneSessionManager => new ZoneSessionManager($this->app));

        $this->app->singleton(ZoneSessionConfigurator::class, fn(Application $app): ZoneSessionConfigurator => new ZoneSessionConfigurator(
            $app->make(Repository::class),
            $this->zoneSessionManager($app),
            (array) $app->make(Repository::class)->get('session', []),
            ! $app->environment('local'),
        ));
    }

    public function boot(ViewFactory $views): void
    {
        $views->composer([self::SIGN_IN_VIEWS, self::ACCOUNT_VIEWS], $this->shareRequestZone(...));
        $views->composer(self::ZONE_LAYOUT_VIEW, $this->shareZoneWithLayout(...));
        $views->composer(self::AUTH_LAYOUT_VIEW, $this->shareAuthLayout(...));

        $this->useZoneRedirects();
        $this->app->afterResolving(HttpKernel::class, $this->useZoneRedirects(...));
    }

    /**
     * Layout strefy dostaje strefę żądania, konto zalogowane w jej guardzie (albo null), a w strefie z `teams`
     * organizacje konta (przełącznik, gdy jest ich więcej niż jedna) i id bieżącej organizacji.
     */
    private function shareZoneWithLayout(View $view): void
    {
        $zone = $this->app->make(CurrentZone::class)->get();
        $account = $this->app->make(CurrentAccount::class)->get();
        $organizations = $zone->teams && $account instanceof Model
            ? $this->app->make(ActiveMemberships::class)->organizations($account)
            : null;

        $view->with('zone', $zone)
            ->with('account', $account)
            ->with('organizations', $organizations !== null && $organizations->count() > 1 ? $organizations : null)
            ->with('currentOrganizationId', $this->app->make(CurrentOrganization::class)->id());
    }

    /**
     * Widoki logowania i konta dostają strefę żądania; poza strefą (podgląd w design systemie) zostaje `$zone`
     * przekazana w danych widoku.
     */
    private function shareRequestZone(View $view): void
    {
        $zone = $this->app->make(CurrentZone::class);

        if ($zone->has()) {
            $view->with('zone', $zone->get());
        }
    }

    /**
     * Layout logowania dostaje układ strefy żądania (`auth.layout`); poza strefą z logowaniem (np. fałszywe
     * logowanie pułapki, podgląd w design systemie) układ domyślny. Prop `layout` komponentu wygrywa.
     */
    private function shareAuthLayout(View $view): void
    {
        $zone = $this->app->make(CurrentZone::class);

        $view->with('zoneAuthLayout', $zone->has() && $zone->get()->requiresLogin() ? $zone->get()->auth()->layout : AuthLayout::Centered);
    }

    /**
     * Przekierowania auth/guest według bieżącej strefy. Ustawiane także po rozwiązaniu kernela HTTP,
     * bo ApplicationBuilder::withMiddleware() ustawia wtedy własne (globalną trasę `login`).
     */
    private function useZoneRedirects(): void
    {
        Authenticate::redirectUsing(fn(): string => $this->app->make(ZoneRedirects::class)->guestRedirect());
        RedirectIfAuthenticated::redirectUsing(fn(): string => $this->app->make(ZoneRedirects::class)->userRedirect());
    }

    /**
     * Menedżer sesji aplikacji (wiązanie `session`, zastąpione przez ZoneSessionManager).
     */
    private function zoneSessionManager(Application $app): ZoneSessionManager
    {
        $manager = $app->make('session');

        return $manager instanceof ZoneSessionManager ? $manager : throw new LogicException('The session manager must be ' . ZoneSessionManager::class . '.');
    }
}
