{{-- Układ stron strefy. Zalogowane konto: layout aplikacji (x-mainboard::app.layout) — awatar w kolorze konta z drugą linią (menu strefy albo bieżąca organizacja) i menu konta (profil, bezpieczeństwo, wygląd, wylogowanie), przełącznik kontekstu (formularze POST: organizacja, gdy konto ma kilka; rola, gdy strefa ma `role_contexts`, a konto kilka ról — $roles, $currentRole), menu i wyszukiwarka strefy (Contracts\ZoneNavigation z Zones\ZoneNavigations), na stronach ustawień menu ustawień z powrotem do aplikacji ($settingsMenu, Zones\ZoneSettingsMenu), stopka simplysmart z odnośnikami prawnymi strefy (`legal_links`), komunikat `status` i toasty z sesji jako toasty (Toast::fromFlash()), kolorystyki strefy (`palettes`) do wyboru na stronie wyglądu (ustawienia konta). Gość (strona publiczna strefy): prosta kolumna treści, komunikaty z sesji też jako toasty. $zone, $account, $organizations, $currentOrganizationId, $currentOrganizationName, $roles, $currentRole, $navigation i $settingsMenu daje Core; meta, back i slot actions trafiają do nagłówka. --}}
@props(['title', 'description' => null, 'keywords' => null, 'tags' => null, 'meta' => null, 'back' => null])
@if ($account !== null)
    @php
        $accountItems = app(\Simplysmart\Mainboard\Zones\ZoneSettingsMenu::class)->accountItems($zone);
        $contexts = array_values(array_filter([
            $organizations === null ? null : new \Simplysmart\Mainboard\View\Navigation\NavigationGroup(text('account.organization'), $organizations->map(
                static fn ($organization): \Simplysmart\Mainboard\View\Navigation\NavigationForm => new \Simplysmart\Mainboard\View\Navigation\NavigationForm($organization->name, route($zone->routeName(\Simplysmart\Mainboard\Http\AccountRoutes::ORGANIZATION_SWITCH)), \Simplysmart\Mainboard\Http\Controllers\Account\OrganizationSwitchController::FIELD, $organization->token, 'building', $organization->id === $currentOrganizationId),
            )->values()->all()),
            $roles === [] ? null : new \Simplysmart\Mainboard\View\Navigation\NavigationGroup(text('account.role'), array_map(
                static fn (string $role): \Simplysmart\Mainboard\View\Navigation\NavigationForm => new \Simplysmart\Mainboard\View\Navigation\NavigationForm($zone->roleLabel($role), route($zone->routeName(\Simplysmart\Mainboard\Http\AccountRoutes::ROLE_SWITCH)), \Simplysmart\Mainboard\Http\Controllers\Account\RoleSwitchController::FIELD, $role, 'user', $role === $currentRole),
                $roles,
            )),
        ]));
        $toasts = \Simplysmart\Mainboard\View\Toast::fromFlash(session(\Simplysmart\Mainboard\View\Toast::SESSION_KEY), session(\Simplysmart\Mainboard\View\Toast::STATUS_KEY));
    @endphp
    <x-mainboard::app.layout
        :title="$title"
        :description="$description"
        :meta="$meta"
        :back="$back"
        :navigation="$settingsMenu?->active ? $settingsMenu->groups : ($navigation?->groups() ?? [])"
        :settings="$settingsMenu"
        :legal-links="$zone->legalLinkTargets()"
        :search="$navigation?->search()"
        :account="[
            'name' => $account->displayName(),
            'subtitle' => $navigation?->subtitle() ?? $currentOrganizationName,
            'color' => $account->avatarColor(),
            'label' => text('sign_in.signed_in_as', ['name' => $account->displayName()]),
            'items' => $accountItems,
        ]"
        :sign-out="route($zone->routeName('auth.logout'))"
        :contexts="$contexts === [] ? null : $contexts"
        :toasts="$toasts"
        :zone-stylesheets="$zone->stylesheets"
        :palettes="$zone->palettes"
        :palette-scope="$zone->name"
    >
        @isset($actions)
            <x-slot:actions>{{ $actions }}</x-slot:actions>
        @endisset

        {{ $slot }}
    </x-mainboard::app.layout>
@else
    <x-mainboard::page :title="$title" :description="$description" :keywords="$keywords" :tags="$tags" :robots="$zone->requiresLogin() ? 'noindex, nofollow' : null" :palettes="$zone->palettes" :palette-scope="$zone->name">
        <x-slot:head>
            <x-mainboard::styles />
            <x-mainboard::zone-styles :paths="$zone->stylesheets" />
            <x-mainboard::scripts :files="['js/toasts.js']" />
        </x-slot:head>

        <main class="ss-page">
            <x-mainboard::logo class="ss-page__logo" />
            <h1 class="ss-page__title">{{ $title }}</h1>

            {{ $slot }}
        </main>

        <x-mainboard::toasts :toasts="\Simplysmart\Mainboard\View\Toast::fromFlash(session(\Simplysmart\Mainboard\View\Toast::SESSION_KEY), session(\Simplysmart\Mainboard\View\Toast::STATUS_KEY))" />
    </x-mainboard::page>
@endif
