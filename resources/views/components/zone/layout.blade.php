{{-- Układ stron strefy. Zalogowane konto: layout aplikacji (x-admiro::app.layout) — awatar w kolorze konta z drugą linią (menu strefy albo bieżąca organizacja) i menu konta (profil, bezpieczeństwo, wygląd), ikony ustawień i powiadomień (dzwonek z pustym panelem — strefy nie mają jeszcze źródła powiadomień), wylogowanie na dole menu, przełącznik kontekstu (formularze POST: organizacja, gdy konto ma kilka; rola, gdy strefa ma `role_contexts`, a konto kilka ról — $roles, $currentRole), menu i wyszukiwarka strefy (Contracts\ZoneNavigation z Zones\ZoneNavigations), na stronach ustawień menu ustawień z powrotem do aplikacji ($settingsMenu, Zones\ZoneSettingsMenu) — w strefie z `app_layout` = workspace (Enums\AppLayout) bez menu ustawień i ikony ustawień: menu strefy z Contracts\ZoneSitemap pokazuje w menu bocznym tylko groups(), a wszystkie strony (sitemap() i grupa konta) w mapie strony w stopce; bez ZoneSitemap grupy ustawień i konta są w głównym menu; na stronach ustawień menu boczne pokazuje tylko menu ustawień z powrotem do aplikacji, stopka simplysmart z odnośnikami prawnymi strefy (`legal_links`), komunikat `status` i toasty z sesji jako toasty (Toast::fromFlash()), kolorystyki strefy (`palettes`) do wyboru na stronie wyglądu (ustawienia konta). Gość (strona publiczna strefy): prosta kolumna treści, komunikaty z sesji też jako toasty. $zone, $account, $organizations, $currentOrganizationId, $currentOrganizationName, $roles, $currentRole, $navigation i $settingsMenu daje Core; meta, back, icon i slot actions trafiają do nagłówka strony (bez icon — ikona bieżącej pozycji mapy strony albo menu). --}}
@props(['title', 'description' => null, 'keywords' => null, 'tags' => null, 'meta' => null, 'back' => null, 'icon' => null])
@if ($account !== null)
    @php
        $accountItems = app(\Simplysmart\Admiro\Zones\ZoneSettingsMenu::class)->accountItems($zone);
        $contexts = array_values(array_filter([
            $organizations === null ? null : new \Simplysmart\Admiro\View\Navigation\NavigationGroup(text('account.organization'), $organizations->map(
                static fn ($organization): \Simplysmart\Admiro\View\Navigation\NavigationForm => new \Simplysmart\Admiro\View\Navigation\NavigationForm($organization->contextName(), route($zone->routeName(\Simplysmart\Admiro\Http\AccountRoutes::ORGANIZATION_SWITCH)), \Simplysmart\Admiro\Http\Controllers\Account\OrganizationSwitchController::FIELD, $organization->token, 'building', $organization->id === $currentOrganizationId),
            )->values()->all()),
            $roles === [] ? null : new \Simplysmart\Admiro\View\Navigation\NavigationGroup(text('account.role'), array_map(
                static fn (string $role): \Simplysmart\Admiro\View\Navigation\NavigationForm => new \Simplysmart\Admiro\View\Navigation\NavigationForm($zone->roleLabel($role), route($zone->routeName(\Simplysmart\Admiro\Http\AccountRoutes::ROLE_SWITCH)), \Simplysmart\Admiro\Http\Controllers\Account\RoleSwitchController::FIELD, $role, 'user', $role === $currentRole),
                $roles,
            )),
        ]));
        $workspace = $zone->appLayout === \Simplysmart\Admiro\Enums\AppLayout::Workspace;
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
        :description="$description"
        :meta="$meta"
        :back="$back"
        :variant="$zone->appLayout"
        :navigation="$menu"
        :sitemap="$sitemap"
        :calendar="$workspace ? app(\Simplysmart\Admiro\View\Calendar\CalendarMonths::class)->current() : null"
        :settings="$workspace && ! $settingsMode ? null : $settingsMenu"
        :legal-links="$zoneLegalLinks ?? []"
        :search="$navigation?->search()"
        :account="[
            'name' => $account->displayName(),
            'subtitle' => $navigation?->subtitle() ?? $currentOrganizationName,
            'color' => $account->avatarColor(),
            'label' => text('sign_in.signed_in_as', ['name' => $account->displayName()]),
            'status' => method_exists($account, 'recordStatus') ? $account->recordStatus()?->label() : null,
            'active' => ! method_exists($account, 'recordStatus') || $account->recordStatus()?->isUsable() !== false,
            'profile' => route($zone->routeName(\Simplysmart\Admiro\Http\AccountRoutes::PROFILE)),
            'social' => array_map(static fn (array $link): array => ['url' => $link['url'], 'label' => $link['network']->label(), 'icon' => $link['network']->icon()], $zone->socialLinks),
            'items' => $accountItems,
        ]"
        :sign-out="route($zone->routeName('auth.logout'))"
        :contexts="$contexts === [] ? null : $contexts"
        :notifications="new \Simplysmart\Admiro\View\Notifications\NotificationPanel([])"
        :toasts="$toasts"
        :zone-stylesheets="$zone->stylesheets"
        :palettes="$zone->palettes"
        :palette-scope="$zone->name"
    >
        @isset($actions)
            <x-slot:actions>{{ $actions }}</x-slot:actions>
        @endisset

        {{ $slot }}
    </x-admiro::app.layout>
@else
    <x-admiro::page :title="$title" :description="$description" :keywords="$keywords" :tags="$tags" :robots="$zone->requiresLogin() ? 'noindex, nofollow' : null" :palettes="$zone->palettes" :palette-scope="$zone->name">
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
