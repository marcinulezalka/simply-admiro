{{-- Układ stron strefy. Zalogowane konto: layout aplikacji (x-admiro::app.layout) — awatar w kolorze konta z drugą linią (menu strefy albo bieżąca organizacja) i menu konta (profil, bezpieczeństwo, wygląd), ikony ustawień i powiadomień (dzwonek; panel z menu strefy, gdy implementuje Contracts\ZoneNotifications, inaczej pusty), wylogowanie na dole menu, przełącznik kontekstu (Zones\ZoneContexts: każda organizacja, jednostka i departament konta z jego rolami w nich; jedno kliknięcie — formularz POST na account.context.switch — ustawia organizację i rolę), menu i wyszukiwarka strefy (Contracts\ZoneNavigation z Zones\ZoneNavigations), na stronach ustawień menu ustawień z powrotem do aplikacji ($settingsMenu, Zones\ZoneSettingsMenu) — w strefie z `app_layout` = workspace (Enums\AppLayout; menu strefy z Contracts\ZoneAppLayout może wybrać wariant dla bieżącego żądania, np. według aktywnej roli) bez menu bocznego, menu ustawień i ikony ustawień: konto, przełącznik kontekstu i powiadomienia są w stopce, a wszystkie strony w mapie strony w stopce (z Contracts\ZoneSitemap: sitemap() i grupa konta; bez niej menu strefy z grupami ustawień i konta), stopka simplysmart z odnośnikami prawnymi strefy (`legal_links`), komunikat `status` i toasty z sesji jako toasty (Toast::fromFlash()), kolorystyki strefy (`palettes`) do wyboru na stronie wyglądu (ustawienia konta), tło płótna z logo strefy przy `backdrop` = zone-logo. Gość (strona publiczna strefy): prosta kolumna treści, komunikaty z sesji też jako toasty. $zone, $account, $organizations, $currentOrganizationId, $currentOrganizationName, $roles, $currentRole, $navigation i $settingsMenu daje Core; meta, back, backLabel, backIcon, icon i slot actions trafiają do nagłówka strony: nad tytułem zawsze odnośnik — „Wstecz” (back, a bez niego ZoneRedirects::backUrl() — poprzednia strona albo dashboard) albo własna etykieta i ikona (backLabel, backIcon), pod tytułem opis meta tylko wtedy, gdy strona go poda; bez icon — ikona bieżącej pozycji mapy strony albo menu. --}}
@props(['title', 'description' => null, 'keywords' => null, 'tags' => null, 'meta' => null, 'back' => null, 'backLabel' => null, 'backIcon' => 'arrow-left', 'icon' => null])
@if ($account !== null)
    @php
        $accountItems = app(\Simplysmart\Admiro\Zones\ZoneSettingsMenu::class)->accountItems($zone);
        $contexts = $account instanceof \Simplysmart\Admiro\Models\ZoneAccount ? app(\Simplysmart\Admiro\Zones\ZoneContexts::class)->for($zone, $account) : [];
        $zoneAppLayout = $navigation instanceof \Simplysmart\Admiro\Contracts\ZoneAppLayout ? $navigation : null;
        $variant = $zoneAppLayout?->appLayout() ?? $zone->appLayout;
        $workspace = $variant === \Simplysmart\Admiro\Enums\AppLayout::Workspace;
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

        $toasts = \Simplysmart\Admiro\View\Toast::fromFlash(session(\Simplysmart\Admiro\View\Toast::SESSION_KEY), session(\Simplysmart\Admiro\View\Toast::STATUS_KEY));
    @endphp
    <x-admiro::app.layout
        :title="$title"
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
    >
        @isset($actions)
            <x-slot:actions :class="$actions->attributes->get('class')">{{ $actions }}</x-slot:actions>
        @endisset

        {{ $slot }}
    </x-admiro::app.layout>
@else
    <x-admiro::page :title="$title" :description="$description" :keywords="$keywords" :tags="$tags" :robots="$zone->requiresLogin() ? 'noindex, nofollow' : null" :palettes="$zone->palettes" :palette-scope="$zone->name" :font-styles="$zone->fontStyles">
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
