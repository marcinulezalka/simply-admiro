{{-- Układ stron strefy. Zalogowane konto: layout aplikacji (x-admiro::app.layout) — awatar w kolorze konta z drugą linią (menu strefy albo bieżąca organizacja) i menu konta (profil, bezpieczeństwo, wygląd), ikony ustawień i powiadomień (dzwonek; panel z menu strefy, gdy implementuje Contracts\ZoneNotifications, inaczej pusty), wylogowanie na dole menu, przełącznik kontekstu (Zones\ZoneContexts: każda organizacja, jednostka i departament konta z jego rolami w nich; jedno kliknięcie — formularz POST na account.context.switch — ustawia organizację i rolę), menu i wyszukiwarka strefy (Contracts\ZoneNavigation z Zones\ZoneNavigations), na stronach ustawień menu ustawień z powrotem do aplikacji ($settingsMenu, Zones\ZoneSettingsMenu) — w strefie z `app_layout` = workspace (Enums\AppLayout; menu strefy z Contracts\ZoneAppLayout może wybrać wariant dla bieżącego żądania, np. według aktywnej roli) bez menu bocznego, menu ustawień i ikony ustawień: konto, przełącznik kontekstu i powiadomienia są w stopce, a wszystkie strony w mapie strony w stopce (z Contracts\ZoneSitemap: sitemap() i grupa konta; bez niej menu strefy z grupami ustawień i konta), stopka simplysmart z odnośnikami prawnymi strefy (`legal_links`), komunikat `status` i toasty z sesji jako toasty (Toast::fromFlash()), kolorystyki strefy (`palettes`) do wyboru na stronie wyglądu (ustawienia konta), w strefie z `appearance.account` wygląd zapisany w koncie (Profile\AccountAppearance: domyślny wygląd aplikacji nadpisany wyborami konta) od razu na <html>, tło płótna z logo strefy przy `backdrop` = zone-logo. Gość (strona publiczna strefy): prosta kolumna treści, komunikaty z sesji też jako toasty. $zone, $account, $organizations, $currentOrganizationId, $currentOrganizationName, $roles, $currentRole, $navigation i $settingsMenu daje Core; meta, eyebrow (mały prefiks nad tytułem), back, backLabel, backIcon, icon i slot actions trafiają do nagłówka strony, a pageTitle (gdy podany) do tytułu karty przeglądarki zamiast title: nad tytułem zawsze odnośnik — „Wstecz” (back, a bez niego ZoneRedirects::backUrl() — poprzednia strona albo dashboard) albo własna etykieta i ikona (backLabel, backIcon), pod tytułem opis meta tylko wtedy, gdy strona go poda; bez icon — ikona bieżącej pozycji mapy strony albo menu. W strefie z layoutem stref (Enums\AppLayout::Zone, z `app_layout` albo Contracts\ZoneAppLayout) strona renderuje x-admiro::app.zone-layout: menu konta tylko z profilem (ZoneSettingsMenu::profileItem()) i wylogowaniem, obok małe wizytówki jednostek konta (ZoneContexts::units()), bez mapy strony, z napisem „TEMP” nad treścią, gdy menu strefy implementuje Contracts\ZonePageProgress i widok jest w przygotowaniu; odnośnik nad tytułem zawsze ze strzałką ← — na stronie startowej (home) „Zmień kontekst” (kotwica przełącznika), gdy konto ma co najmniej dwa konteksty, a inaczej „Wyloguj” (formularz POST), na pozostałych „Wstecz”. --}}
@props(['title', 'pageTitle' => null, 'eyebrow' => null, 'description' => null, 'keywords' => null, 'tags' => null, 'meta' => null, 'back' => null, 'backLabel' => null, 'backIcon' => 'arrow-left', 'icon' => null, 'home' => false])
@if ($account !== null)
    @php
        $accountItems = app(\Simplysmart\Admiro\Zones\ZoneSettingsMenu::class)->accountItems($zone);
        $contexts = $account instanceof \Simplysmart\Admiro\Models\ZoneAccount ? app(\Simplysmart\Admiro\Zones\ZoneContexts::class)->for($zone, $account) : [];
        $zoneAppLayout = $navigation instanceof \Simplysmart\Admiro\Contracts\ZoneAppLayout ? $navigation : null;
        $variant = $zoneAppLayout?->appLayout() ?? $zone->appLayout;
        $zoneLayout = $variant === \Simplysmart\Admiro\Enums\AppLayout::Zone;
        $workspace = $variant === \Simplysmart\Admiro\Enums\AppLayout::Workspace || $zoneLayout;
        $hasSitemap = $navigation instanceof \Simplysmart\Admiro\Contracts\ZoneSitemap;
        $settingsMode = $workspace && ($settingsMenu?->active ?? false);
        $menu = match (true) {
            $settingsMode => $settingsMenu->groups,
            $workspace && $hasSitemap => $navigation->groups(),
            $workspace => [...($navigation?->groups() ?? []), ...($settingsMenu->groups ?? [])],
            default => $settingsMenu?->active ? $settingsMenu->groups : ($navigation?->groups() ?? []),
        };
        $sitemap = $workspace && $hasSitemap
            ? [...$navigation->sitemap(), new \Simplysmart\Admiro\View\Navigation\NavigationGroup(text('account.settings_group'), $accountItems)]
            : null;
        $pageIcon = $icon;

        foreach ([...($sitemap ?? []), ...$menu] as $group) {
            foreach ($group->items as $item) {
                if ($pageIcon === null && $item instanceof \Simplysmart\Admiro\View\Navigation\NavigationItem && ($item->current || $item->isExpanded())) {
                    $pageIcon = $item->icon;
                }
            }
        }

        $appearanceAttributes = app(\Simplysmart\Admiro\Profile\AccountAppearance::class)->htmlAttributes($zone, $account instanceof \Illuminate\Database\Eloquent\Model ? $account : null);
        $appearanceUrl = $zone->appearance->account ? route($zone->routeName(\Simplysmart\Admiro\Http\AccountRoutes::APPEARANCE_UPDATE)) : null;
        $toasts = \Simplysmart\Admiro\View\Toast::fromFlash(session(\Simplysmart\Admiro\View\Toast::SESSION_KEY), session(\Simplysmart\Admiro\View\Toast::STATUS_KEY));
        $contextCount = array_sum(array_map(static fn (\Simplysmart\Admiro\View\Navigation\NavigationGroup $group): int => count($group->items), $contexts));
    @endphp
    @if ($zoneLayout)
    <x-admiro::app.zone-layout
        :title="$title"
        :page-title="$pageTitle"
        :eyebrow="$eyebrow"
        :icon="$pageIcon"
        :meta="$meta"
        :description="$description"
        :back="$home ? ($contextCount > 1 ? '#' . \Simplysmart\Admiro\View\Navigation\NavigationItem::CONTEXT_SWITCH_ANCHOR : null) : ($back ?? app(\Simplysmart\Admiro\Zones\ZoneRedirects::class)->backUrl())"
        :back-label="$home ? ($contextCount > 1 ? text('layout.change_context') : null) : $backLabel"
        :back-sign-out="$home && $contextCount <= 1 ? route($zone->routeName('auth.logout')) : null"
        :settings-area="$zoneAppLayout?->settingsArea() ?? false"
        :backdrop-logo="app(\Simplysmart\Admiro\View\ZoneLogos::class)->backdrop($zone)"
        :shortcuts="$navigation instanceof \Simplysmart\Admiro\Contracts\ZoneShortcuts ? $navigation->shortcuts() : []"
        :calendar="app(\Simplysmart\Admiro\View\Calendar\CalendarMonths::class)->current()"
        :settings="$settingsMode ? $settingsMenu : null"
        :settings-url="$settingsMenu?->url"
        :search="$navigation?->search()"
        :account="[
            'name' => $account->displayName(),
            'identifier' => $account instanceof \Simplysmart\Admiro\Models\ZoneAccount ? $account->accountIdentifier() : null,
            'color' => $account->avatarColor(),
            'label' => text('sign_in.signed_in_as', ['name' => $account->displayName()]),
            'profile' => app(\Simplysmart\Admiro\Zones\ZoneSettingsMenu::class)->profileItem($zone),
            'units' => $account instanceof \Simplysmart\Admiro\Models\ZoneAccount ? app(\Simplysmart\Admiro\Zones\ZoneContexts::class)->units($zone, $account) : [],
        ]"
        :sign-out="route($zone->routeName('auth.logout'))"
        :contexts="$contexts === [] ? null : $contexts"
        :notifications="$navigation instanceof \Simplysmart\Admiro\Contracts\ZoneNotifications ? $navigation->notifications() : new \Simplysmart\Admiro\View\Notifications\NotificationPanel([])"
        :toasts="$toasts"
        :in-progress="$navigation instanceof \Simplysmart\Admiro\Contracts\ZonePageProgress && $navigation->pageInProgress()"
        :zone-stylesheets="$zone->stylesheets"
        :palettes="$zone->palettes"
        :palette-scope="$zone->name"
        :font-styles="$zone->fontStyles"
        :appearance="$appearanceAttributes"
        :appearance-url="$appearanceUrl"
    >
        @isset($actions)
            <x-slot:actions :class="$actions->attributes->get('class')">{{ $actions }}</x-slot:actions>
        @endisset

        {{ $slot }}
    </x-admiro::app.zone-layout>
    @else
    <x-admiro::app.layout
        :title="$title"
        :page-title="$pageTitle"
        :eyebrow="$eyebrow"
        :icon="$pageIcon"
        :meta="$meta"
        :description="$description"
        :back="$back ?? app(\Simplysmart\Admiro\Zones\ZoneRedirects::class)->backUrl()"
        :back-label="$backLabel"
        :back-icon="$backIcon"
        :variant="$variant"
        :settings-area="$workspace && ($zoneAppLayout?->settingsArea() ?? false)"
        :backdrop-logo="app(\Simplysmart\Admiro\View\ZoneLogos::class)->backdrop($zone)"
        :navigation="$menu"
        :sitemap="$sitemap"
        :shortcuts="$workspace && $navigation instanceof \Simplysmart\Admiro\Contracts\ZoneShortcuts ? $navigation->shortcuts() : []"
        :calendar="$workspace ? app(\Simplysmart\Admiro\View\Calendar\CalendarMonths::class)->current() : null"
        :settings="$workspace && ! $settingsMode ? null : $settingsMenu"
        :settings-url="$settingsMenu?->url"
        :legal-links="$zoneLegalLinks ?? []"
        :search="$navigation?->search()"
        :account="[
            'name' => $account->displayName(),
            'identifier' => $account instanceof \Simplysmart\Admiro\Models\ZoneAccount ? $account->accountIdentifier() : null,
            'subtitle' => $navigation?->subtitle() ?? $currentOrganizationName,
            'color' => $account->avatarColor(),
            'label' => text('sign_in.signed_in_as', ['name' => $account->displayName()]),
            'status' => method_exists($account, 'recordStatus') ? $account->recordStatus()?->label() : null,
            'active' => ! method_exists($account, 'recordStatus') || $account->recordStatus()?->isUsable() !== false,
            'social' => array_map(static fn (array $link): array => ['url' => $link['url'], 'label' => $link['network']->label(), 'icon' => $link['network']->icon()], $zone->socialLinks),
            'items' => $accountItems,
        ]"
        :sign-out="route($zone->routeName('auth.logout'))"
        :contexts="$contexts === [] ? null : $contexts"
        :notifications="$navigation instanceof \Simplysmart\Admiro\Contracts\ZoneNotifications ? $navigation->notifications() : new \Simplysmart\Admiro\View\Notifications\NotificationPanel([])"
        :toasts="$toasts"
        :zone-stylesheets="$zone->stylesheets"
        :palettes="$zone->palettes"
        :palette-scope="$zone->name"
        :font-styles="$zone->fontStyles"
        :appearance="$appearanceAttributes"
        :appearance-url="$appearanceUrl"
    >
        @isset($actions)
            <x-slot:actions :class="$actions->attributes->get('class')">{{ $actions }}</x-slot:actions>
        @endisset

        {{ $slot }}
    </x-admiro::app.layout>
    @endif
@else
    <x-admiro::page :title="$pageTitle ?? $title" :description="$description" :keywords="$keywords" :tags="$tags" :robots="$zone->requiresLogin() ? 'noindex, nofollow' : null" :palettes="$zone->palettes" :palette-scope="$zone->name" :font-styles="$zone->fontStyles">
        <x-slot:head>
            <x-admiro::styles />
            <x-admiro::zone-styles :paths="$zone->stylesheets" />
            <x-admiro::scripts :files="['js/toasts.js']" />
        </x-slot:head>

        <main class="ss-page">
            <x-admiro::logo class="ss-page__logo" />
            <h1 class="ss-page__title">{{ $title }}</h1>

            {{ $slot }}
        </main>

        <x-admiro::toasts :toasts="\Simplysmart\Admiro\View\Toast::fromFlash(session(\Simplysmart\Admiro\View\Toast::SESSION_KEY), session(\Simplysmart\Admiro\View\Toast::STATUS_KEY))" />
    </x-admiro::page>
@endif
