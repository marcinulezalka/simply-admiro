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
use Simplysmart\Admiro\Mail\MailBrandGroups;
use Simplysmart\Admiro\Auth\AuthBrand;
use Simplysmart\Admiro\Contracts\CurrentAccount;
use Simplysmart\Admiro\Contracts\CurrentOrganization;
use Simplysmart\Admiro\Contracts\CurrentRole;
use Simplysmart\Admiro\Contracts\CurrentZone;
use Simplysmart\Admiro\Contracts\ZoneRegistry;
use Simplysmart\Admiro\Enums\AuthLayout;
use Simplysmart\Admiro\Enums\FaviconType;
use Simplysmart\Admiro\Http\SignInRoutes;
use Simplysmart\Admiro\Models\ZoneAccount;
use Simplysmart\Admiro\Legal\LegalLinks;
use Simplysmart\Admiro\Permissions\RoleSelector;
use Simplysmart\Admiro\Queries\Organizations\ActiveMemberships;
use Simplysmart\Admiro\Translation\CascadeTextTranslator;
use Simplysmart\Admiro\Zones\InMemoryZoneRegistry;
use Simplysmart\Admiro\Zones\RequestAccount;
use Simplysmart\Admiro\Zones\RequestZone;
use Simplysmart\Admiro\Zones\ZoneNavigations;
use Simplysmart\Admiro\Zones\ZoneRedirects;
use Simplysmart\Admiro\Zones\ZoneRouteRegistrar;
use Simplysmart\Admiro\Zones\ZoneSessionConfigurator;
use Simplysmart\Admiro\Zones\ZoneSessionManager;
use Simplysmart\Admiro\Zones\ZoneSettingsMenu;

/**
 * Mechanizm stref: rejestr (singleton), strefa i konto żądania, osobny magazyn sesji każdej strefy,
 * przekierowania auth/guest według strefy oraz dane stref dla widoków (layout strefy, logowanie i jego układ, konto,
 * ikony strony w szablonie bazowym).
 */
final class ZonesServiceProvider extends ServiceProvider
{
    private const string SIGN_IN_VIEWS = CascadeTextTranslator::CORE_NAMESPACE . '::auth.*';

    private const string ACCOUNT_VIEWS = CascadeTextTranslator::CORE_NAMESPACE . '::account.*';

    private const string ZONE_LAYOUT_VIEW = CascadeTextTranslator::CORE_NAMESPACE . '::components.zone.layout';

    private const string AUTH_LAYOUT_VIEW = CascadeTextTranslator::CORE_NAMESPACE . '::components.auth.layout';

    private const string PAGE_VIEW = CascadeTextTranslator::CORE_NAMESPACE . '::components.page';

    private const string CONTENT_LAYOUT_VIEW = CascadeTextTranslator::CORE_NAMESPACE . '::components.content.layout';

    public function register(): void
    {
        $this->app->singleton(ZoneRegistry::class, InMemoryZoneRegistry::class);
        $this->app->scoped(CurrentZone::class, RequestZone::class);
        $this->app->bind(CurrentAccount::class, RequestAccount::class);
        $this->app->singleton(ZoneNavigations::class);
        $this->app->singleton(MailBrandGroups::class);

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
        $views->composer(self::PAGE_VIEW, $this->shareFavicons(...));
        $views->composer(self::CONTENT_LAYOUT_VIEW, $this->shareZoneStylesheets(...));

        $this->useZoneRedirects();
        $this->app->afterResolving(HttpKernel::class, $this->useZoneRedirects(...));
    }

    /**
     * Layout strefy dostaje strefę żądania, konto zalogowane w jej guardzie (albo null), w strefie z `teams`
     * organizacje konta (przełącznik, gdy jest ich więcej niż jedna), id i nazwę skróconą (bez niej pełną; departament z jednostką,
     * Organization::contextName()) bieżącej organizacji, w strefie
     * z `role_contexts` role konta do przełączania (co najmniej dwie) i aktywną rolę, menu strefy z
     * Zones\ZoneNavigations oraz menu ustawień (Zones\ZoneSettingsMenu) dla zalogowanego konta.
     */
    private function shareZoneWithLayout(View $view): void
    {
        $zone = $this->app->make(CurrentZone::class)->get();
        $account = $this->app->make(CurrentAccount::class)->get();
        $organizations = $zone->teams && $account instanceof Model
            ? $this->app->make(ActiveMemberships::class)->organizations($account)
            : null;
        $currentId = $this->app->make(CurrentOrganization::class)->id();

        $navigation = $account !== null ? $this->app->make(ZoneNavigations::class)->for($zone->name) : null;

        $view->with('zone', $zone)
            ->with('account', $account)
            ->with('organizations', $organizations !== null && $organizations->count() > 1 ? $organizations : null)
            ->with('currentOrganizationId', $currentId)
            ->with('currentOrganizationName', $organizations?->firstWhere('id', $currentId)?->contextName(short: true))
            ->with('roles', $account instanceof ZoneAccount ? $this->app->make(RoleSelector::class)->choices($zone, $account, $currentId) : [])
            ->with('currentRole', $account !== null ? $this->app->make(CurrentRole::class)->of($account, $currentId) : null)
            ->with('navigation', $navigation)
            ->with('zoneLegalLinks', $this->app->make(LegalLinks::class)->for($zone))
            ->with('settingsMenu', $account !== null ? $this->app->make(ZoneSettingsMenu::class)->for($zone, $navigation) : null);
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
     * Layout logowania dostaje układ strefy żądania (`auth.layout`), adresy jej banera (`auth.brand`, wersja
     * w adresie) i treść jej logo SVG (`auth.logo`); poza strefą z logowaniem (np. fałszywe logowanie pułapki,
     * podgląd w design systemie) układ domyślny i logo. Do tego arkusze (`stylesheets`), kolorystyki (`palettes`), kroje (`font_styles`) i odnośniki prawne (`legal_links`, adresy i etykiety)
     * strefy żądania, a także jej tytuł karty przeglądarki (tekst `page_title`, pusty = nagłówek ekranu). Prop `layout` komponentu wygrywa.
     */
    private function shareAuthLayout(View $view): void
    {
        $zone = $this->app->make(CurrentZone::class);
        $settings = $zone->has() && $zone->get()->requiresLogin() ? $zone->get()->auth() : null;
        $brand = $settings?->brand;
        $brandUrl = static fn(string $variant): string => route($zone->get()->routeName('auth.' . SignInRoutes::BRAND), ['variant' => $variant, 'v' => $brand?->version($variant)]);

        $view->with('zoneAuthLayout', $settings->layout ?? AuthLayout::Centered)
            ->with('zoneStylesheets', $zone->has() ? $zone->get()->stylesheets : [])
            ->with('zonePalettes', $zone->has() ? $zone->get()->palettes : [])
            ->with('zonePaletteScope', $zone->has() ? $zone->get()->name : '')
            ->with('zoneFontStyles', $zone->has() ? $zone->get()->fontStyles : [])
            ->with('zoneAuthPageTitle', $settings === null ? '' : $zone->get()->signInText('page_title'))
            ->with('zoneLegalLinks', $settings === null ? [] : array_map(
                static function (array $link) use ($zone): array {
                    $label = $zone->get()->signInText('legal_' . $link['key']);

                    return ['url' => $link['url'], 'label' => $label !== '' && $label !== 'sign_in.legal_' . $link['key'] ? $label : $link['label']];
                },
                $this->app->make(LegalLinks::class)->for($zone->get()),
            ))
            ->with('zoneAuthCookieLinks', [
                'first' => $settings->cookiePolicyFirst ?? false,
                'settings' => $settings->cookieSettingsLink ?? true,
                'policyLabel' => $settings === null || $zone->get()->signInText('legal_cookies') === '' ? null : $zone->get()->signInText('legal_cookies'),
            ])
            ->with('zoneAuthHomeLink', $settings->homeLink ?? true)
            ->with('zoneAuthLogo', $settings?->logo === null ? null : (file_get_contents($settings->logo) ?: null))
            ->with('zoneAuthBrand', $brand === null ? null : [
                'light' => $brandUrl(AuthBrand::LIGHT),
                'dark' => $brand->dark !== null ? $brandUrl(AuthBrand::DARK) : null,
                'alt' => $brand->alt !== '' ? text($brand->alt) : '',
            ]);
    }

    /**
     * Strona treści w strefie (np. dokumenty prawne strefy) dostaje arkusze strefy żądania (`stylesheets`), jak logowanie;
     * poza strefą (np. polityka cookies Core) lista jest pusta.
     */
    private function shareZoneStylesheets(View $view): void
    {
        $zone = $this->app->make(CurrentZone::class);

        $view->with('zoneStylesheets', $zone->has() ? $zone->get()->stylesheets : []);
    }

    /**
     * Szablon bazowy strony dostaje znaczniki ikon strefy żądania (`favicon`, adresy z wersją; poza strefą albo bez
     * `favicon` lista jest pusta — ikona aplikacji z `public/favicon.ico`) i reguły `@font-face` jej fontów (`fonts`,
     * adresy z wersją; bez nich pusty tekst — font Core).
     */
    private function shareFavicons(View $view): void
    {
        $zone = $this->app->make(CurrentZone::class);
        $favicon = $zone->has() ? $zone->get()->favicon : null;

        $fonts = $zone->has() ? $zone->get()->fonts : null;
        $view->with('zoneFontFaces', $fonts?->fontFaceCss(static fn(string $key, string $version): string => route($zone->get()->routeName(ZoneRouteRegistrar::FONT), ['font' => $key, 'v' => $version])) ?? '');

        $view->with('zoneFavicons', $favicon === null ? [] : array_map(static fn(FaviconType $type): array => [
            'rel' => $type->rel(),
            'href' => route($zone->get()->routeName(ZoneRouteRegistrar::FAVICON), ['type' => $type->value, 'v' => $favicon->version($type)]),
            'type' => $type->mediaType(),
            'sizes' => $type->sizes(),
        ], $favicon->types()));
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
