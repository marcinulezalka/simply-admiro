{{-- Bazowy layout stref po zalogowaniu (Enums\AppLayout::Zone, docs/design-system.md §5), oddzielony od layoutu strefy administratorów (x-admiro::app.layout): bez menu bocznego, top baru i mapy strony. Tło: płótno z logo strefy przy dolnej krawędzi okna (x-admiro::app.backdrop; backdropLogo: treść SVG, bez niego logotyp simplysmart). Nagłówek (x-admiro::app.context): ikona strony (icon), nad tytułem zawsze odnośnik ze strzałką ← — „Wstecz” (back), własna etykieta (backLabel, np. „Zmień kontekst” z kotwicą przełącznika) albo „Wyloguj” jako formularz POST (backSignOut) —, mały prefiks (eyebrow), duży tytuł, opis meta i slot actions. Treść: slot, a na stronie ustawień (settingsArea i settings->active) obszar kategorii x-admiro::app.settings-area. inProgress: widok strony w przygotowaniu — na środku treści duży, półprzezroczysty napis „TEMP” (.ss-temp-mark; Contracts\ZonePageProgress). Stopka przyklejona do dołu (klasy stopki Workspace .ss-workspace-footer z .ss-zone-footer): po lewej konto (awatar, nazwa, identyfikator krojem mono — account.identifier) z menu nad stopką tylko z „Mój profil” (account.profile: ?View\Navigation\NavigationItem) i „Wyloguj” (signOut); na środku wyszukiwarka x-admiro::app.search compose z suwakami za polem (trailing-scopes): „+”, błyskawica (search->findActions), pole, zakresy, Enter; po prawej ramka narzędzi: przełącznik kontekstu z rolami wciętymi pod organizacjami (x-admiro::app.context-switch tree), przycisk „Zablokuj ekran” (ikona lock-screen; jak Win+L: formularz POST na lockUrl — Auth\ScreenLock, konto zostaje zalogowane, a do pracy wraca się PIN-em) i data otwierająca dwa panele boczne jak harmonijka (x-admiro::app.side-panels: kalendarz z narzędziami wyglądu i powiadomienia; koło zębate ustawień — pierwszy skrót z ikoną settings z shortcuts, a bez niego settingsUrl). account: ?array{name: string, identifier?: ?string, image?: string, color?: Enums\AvatarColor, label?: string, profile?: ?View\Navigation\NavigationItem, units?: list<array{organization: Models\Organization, kind: string, icon: string, current: bool}>} (units: jednostki konta jako lista w drugiej kolumnie menu konta — x-admiro::organization.item: ikona, nazwa, rodzaj i NIP; bieżąca wyróżniona — Zones\ZoneContexts::units()); contexts: ?list<View\Navigation\NavigationGroup>; notifications: ?View\Notifications\NotificationPanel; calendar: ?View\Calendar\CalendarMonth; toasts: list<View\Toast> albo null = z sesji; pozostałe props jak w x-admiro::app.layout (wygląd, kolorystyki, kroje, arkusze). Sloty: actions, before. --}}
@props(['title', 'pageTitle' => null, 'eyebrow' => null, 'icon' => null, 'meta' => null, 'back' => null, 'backLabel' => null, 'backSignOut' => null, 'shortcuts' => [], 'calendar' => null, 'settings' => null, 'settingsUrl' => null, 'settingsArea' => false, 'search' => null, 'account' => null, 'signOut' => null, 'contexts' => null, 'lockUrl' => null, 'notifications' => null, 'toasts' => null, 'inProgress' => false, 'description' => null, 'stylesheets' => [], 'zoneStylesheets' => [], 'palettes' => [], 'paletteScope' => '', 'fontStyles' => [], 'fontFaces' => null, 'backdropLogo' => null, 'appearance' => [], 'appearanceUrl' => null])
@php($toasts ??= \Simplysmart\Admiro\View\Toast::fromSession(session(\Simplysmart\Admiro\View\Toast::SESSION_KEY)))
<x-admiro::page :title="$pageTitle ?? $title" :description="$description" robots="noindex, nofollow" :palettes="$palettes" :palette-scope="$paletteScope" :font-styles="$fontStyles" :font-faces="$fontFaces" :appearance="$appearance" :appearance-url="$appearanceUrl">
    <x-slot:head>
        <x-admiro::app.head :stylesheets="$stylesheets" :zone-stylesheets="$zoneStylesheets" />
    </x-slot:head>

    {{ $before ?? '' }}

    <div class="ss-footer-host">
    <div class="ss-shell ss-footer-stage ss-shell--workspace ss-shell--zone" data-ss-shell>
        <x-admiro::app.backdrop :logo="$backdropLogo" />

        <div class="ss-shell__main">
            <main @class(['ss-main', 'ss-main--in-progress' => $inProgress])>
                <x-admiro::app.context :title="$title" :eyebrow="$eyebrow" :meta="$meta" :back="$back" :back-label="$backLabel" :back-sign-out="$backSignOut" :icon="$icon">
                    @isset($actions)
                        <x-slot:actions :class="$actions->attributes->get('class')">{{ $actions }}</x-slot:actions>
                    @endisset
                </x-admiro::app.context>

                @if ($settingsArea && $settings?->active)
                    <x-admiro::app.settings-area :settings="$settings">{{ $slot }}</x-admiro::app.settings-area>
                @else
                    {{ $slot }}
                @endif

                @if ($inProgress)
                    <p class="ss-temp-mark" aria-hidden="true">{{ text('layout.in_progress') }}</p>
                @endif
            </main>

            <footer class="ss-workspace-footer ss-zone-footer">
                <div class="ss-workspace-footer__bar">
                    @if ($account !== null)
                        <details class="ss-account-menu ss-workspace-footer__account" data-ss-dropdown>
                            <summary class="ss-workspace-footer__account-toggle">
                                <x-admiro::avatar :name="$account['name']" :image="$account['image'] ?? null" :color="$account['color'] ?? null" :accent="! isset($account['color'])" />
                                <span class="ss-workspace-footer__account-text">
                                    <span class="ss-workspace-footer__account-name">{{ $account['name'] }}</span>
                                    @if (filled($account['identifier'] ?? null))
                                        <span class="ss-workspace-footer__account-id">{{ $account['identifier'] }}</span>
                                    @endif
                                </span>
                                <x-admiro::icon name="chevron-down" class="ss-workspace-footer__account-chevron" />
                                <span class="ss-sr-only">{{ $account['label'] ?? text('account.menu') }}</span>
                            </summary>
                            <div @class(['ss-account-menu__panel', 'ss-workspace-footer__account-panel', 'ss-zone-account' => ($account['units'] ?? []) !== []])>
                                <div class="ss-zone-account__menu">
                                    @if (($account['profile'] ?? null) !== null)
                                        <ul class="ss-account-menu__list">
                                            <li><a class="ss-account-menu__link" href="{{ $account['profile']->url }}" @if ($account['profile']->current) aria-current="page" @endif><x-admiro::icon :name="$account['profile']->icon" class="ss-account-menu__icon" />{{ $account['profile']->label }}</a></li>
                                        </ul>
                                    @endif
                                    @if ($signOut !== null)
                                        <form class="ss-workspace-footer__sign-out" method="POST" action="{{ $signOut }}">
                                            @csrf
                                            <button class="ss-account-menu__link" type="submit"><x-admiro::icon name="log-out" class="ss-account-menu__icon" />{{ text('sign_in.sign_out') }}</button>
                                        </form>
                                    @endif
                                </div>
                                @if (($account['units'] ?? []) !== [])
                                    <div class="ss-zone-account__units" role="group" aria-label="{{ text('layout.account_units') }}">
                                        <p class="ss-zone-account__title">{{ text('layout.account_units') }}</p>
                                        @foreach ($account['units'] as $unit)
                                            <x-admiro::organization.item :organization="$unit['organization']" :kind="$unit['kind']" :icon="$unit['icon']" :current="$unit['current']" />
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </details>
                    @endif
                    <div class="ss-workspace-footer__search">
                        @if ($search !== null)
                            <x-admiro::app.search :search="$search" compose trailing-scopes />
                        @endif
                    </div>
                    <div class="ss-workspace-footer__tools">
                        @if ($contexts !== null && $contexts !== [])
                            <x-admiro::app.context-switch :contexts="$contexts" toggle-class="ss-icon-button ss-workspace-footer__tool" tree />
                        @endif
                        @if ($lockUrl !== null)
                            <form class="ss-workspace-footer__lock" method="POST" action="{{ $lockUrl }}">
                                @csrf
                                <button class="ss-icon-button ss-workspace-footer__tool" type="submit" title="{{ text('layout.lock_screen') }}"><x-admiro::icon name="lock-screen" class="ss-icon-button__icon" /><span class="ss-sr-only">{{ text('layout.lock_screen') }}</span></button>
                            </form>
                        @endif
                        @if ($calendar !== null)
                            <x-admiro::app.side-panels :calendar="$calendar" :notifications="$notifications" :toasts="$toasts" :settings-url="collect($shortcuts)->first(static fn ($item): bool => $item->icon === 'settings')?->url ?? $settingsUrl" />
                        @endif
                    </div>
                </div>
            </footer>
        </div>
    </div>
    </div>

    <x-admiro::toasts :toasts="$toasts" />
</x-admiro::page>
