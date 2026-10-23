{{-- Układ stron strefy. Zalogowane konto: layout aplikacji (x-mainboard::app.layout) — awatar w kolorze konta z drugą linią (menu strefy albo bieżąca organizacja) i menu konta (profil, PIN, urządzenia, wylogowanie), przełącznik organizacji (formularze POST, gdy konto ma kilka), menu i wyszukiwarka strefy (Contracts\ZoneNavigation z Zones\ZoneNavigations), komunikat `status` i toasty z sesji jako toasty, kolorystyki strefy (`palettes`) w przełączniku obok motywu. Gość (strona publiczna strefy): prosta kolumna treści. $zone, $account, $organizations, $currentOrganizationId, $currentOrganizationName i $navigation daje Core; meta, back i slot actions trafiają do nagłówka. --}}
@props(['title', 'description' => null, 'keywords' => null, 'tags' => null, 'meta' => null, 'back' => null])
@if ($account !== null)
    @php
        $accountItem = static fn (string $route, string $label, string $icon): \Simplysmart\Mainboard\View\Navigation\NavigationItem => new \Simplysmart\Mainboard\View\Navigation\NavigationItem($label, route($zone->routeName($route)), $icon, request()->routeIs($zone->routeName($route)));
        $accountItems = [
            $accountItem(\Simplysmart\Mainboard\Http\AccountRoutes::PROFILE, text('account.profile_title'), 'user'),
            $accountItem(\Simplysmart\Mainboard\Http\AccountRoutes::PIN, text('account.pin_title'), 'lock'),
        ];
        if ($zone->auth()->trustedDevicesEnabled) {
            $accountItems[] = $accountItem(\Simplysmart\Mainboard\Http\AccountRoutes::DEVICES, text('account.devices_title'), 'shield-check');
        }
        $contexts = $organizations === null ? null : [new \Simplysmart\Mainboard\View\Navigation\NavigationGroup(text('account.organization'), $organizations->map(
            static fn ($organization): \Simplysmart\Mainboard\View\Navigation\NavigationForm => new \Simplysmart\Mainboard\View\Navigation\NavigationForm($organization->name, route($zone->routeName(\Simplysmart\Mainboard\Http\AccountRoutes::ORGANIZATION_SWITCH)), \Simplysmart\Mainboard\Http\Controllers\Account\OrganizationSwitchController::FIELD, $organization->token, 'building', $organization->id === $currentOrganizationId),
        )->values()->all())];
        $toasts = \Simplysmart\Mainboard\View\Toast::fromSession(session(\Simplysmart\Mainboard\View\Toast::SESSION_KEY));
        if (filled(session('status'))) {
            $toasts[] = new \Simplysmart\Mainboard\View\Toast(\Simplysmart\Mainboard\Enums\ToastType::Success, (string) session('status'));
        }
    @endphp
    <x-mainboard::app.layout
        :title="$title"
        :description="$description"
        :meta="$meta"
        :back="$back"
        :navigation="$navigation?->groups() ?? []"
        :search="$navigation?->search()"
        :account="[
            'name' => $account->displayName(),
            'subtitle' => $navigation?->subtitle() ?? $currentOrganizationName,
            'color' => $account->avatarColor(),
            'label' => text('sign_in.signed_in_as', ['name' => $account->displayName()]),
            'items' => $accountItems,
        ]"
        :sign-out="route($zone->routeName('auth.logout'))"
        :contexts="$contexts"
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
        </x-slot:head>

        <main class="ss-page">
            <x-mainboard::logo class="ss-page__logo" />
            <h1 class="ss-page__title">{{ $title }}</h1>

            @if (session('status'))
                <p class="ss-notice" role="status">{{ session('status') }}</p>
            @endif

            {{ $slot }}
        </main>
    </x-mainboard::page>
@endif
