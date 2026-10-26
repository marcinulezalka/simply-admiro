{{-- Layout aplikacji z menu bocznym (docs/design-system.md §5). Menu po lewej przy krawędziach okna (stała szerokość, dłuższe pozycje i nazwy zawijają się): u góry awatar z imieniem i nazwiskiem (albo nazwą) i drugą linią (organizacja, rola) — kliknięcie otwiera menu konta; pod nim zwijanie menu, przełącznik kontekstu, ustawienia (ikona suwaków, settings->url; bieżące na stronach ustawień) i dzwonek powiadomień; pozycje menu (pozycja z podpozycjami — NavigationItem::$children — jest gałęzią rozwijaną <details> z chevronem, podpozycje połączone liniami drzewa; w menu zwiniętym do ikon zamiast niej odnośnik pozycji); na dole przycisk „Wyloguj” (formularz POST na signOut) i subtelne, wyśrodkowane logo simplysmart (35% szerokości), które odsłania pełnoszeroką stopkę strony simplysmart jak na logowaniu (x-admiro::brand-footer: powłoka .ss-shell przesuwa się w górę nad stopką; na ekranach bez stałego menu stopka na końcu strony). Na stronie ustawień (settings->active) menu pokazuje grupy ustawień z powrotem do aplikacji i tytułem „Ustawienia” — navigation zawiera wtedy grupy ustawień. Menu zwija się do ikon (stan w przeglądarce), na telefonie wysuwa się z boku (przycisk w top barze). Top bar: globalna wyszukiwarka (skrót „/”). navigation: list<View\Navigation\NavigationGroup>; settings: ?View\Navigation\SettingsMenu (null = bez ustawień, np. podgląd w design systemie); search: ?View\Navigation\SearchBox; account: ?array{name: string, subtitle?: string, image?: string, color?: Enums\AvatarColor, label?: string, items?: list<View\Navigation\NavigationItem>} (label: opis konta dla czytników ekranu) (bez koloru awatar w kolorze akcentu); signOut: ?string (adres wylogowania: formularz POST na dole menu); contexts: ?list<View\Navigation\NavigationGroup> (przełącznik kontekstu, np. organizacja i rola; pozycje NavigationItem albo NavigationForm, bieżący przez current); notifications: ?View\Notifications\NotificationPanel; toasts: list<View\Toast> albo null = z sesji (Toast::SESSION_KEY); legalLinks: list<array{url: string, label: string}> (odnośniki prawne w stopce simplysmart); stylesheets: dodatkowe arkusze z public/assets/ po arkuszach Core; zoneStylesheets: arkusze strefy (ścieżki plików) na końcu; fontFaces: reguły @font-face strefy (podgląd strefy w design systemie, poza strefą); palettes: list<Enums\Palette> (kolorystyki strony, bez przełącznika w layoucie) i paletteScope: zakres zapamiętania wyboru; motyw i kolorystykę zmienia strona wyglądu w ustawieniach; sloty: actions (akcje nagłówka), before (pasek nad aplikacją, np. podgląd w design systemie), brand (zamiast logo i stopki simplysmart). Menu rozwijane działają bez skryptu (<details>). --}}
@props(['title', 'meta' => null, 'back' => null, 'navigation' => [], 'settings' => null, 'search' => null, 'account' => null, 'signOut' => null, 'contexts' => null, 'notifications' => null, 'toasts' => null, 'legalLinks' => [], 'description' => null, 'stylesheets' => [], 'zoneStylesheets' => [], 'palettes' => [], 'paletteScope' => '', 'fontFaces' => null])
@php($toasts ??= \Simplysmart\Admiro\View\Toast::fromSession(session(\Simplysmart\Admiro\View\Toast::SESSION_KEY)))
<x-admiro::page :title="$title" :description="$description" robots="noindex, nofollow" :palettes="$palettes" :palette-scope="$paletteScope" :font-faces="$fontFaces">
    <x-slot:head>
        <x-admiro::styles :files="[...\Simplysmart\Admiro\View\CoreAssets::STYLESHEETS, ...$stylesheets]" />
        <x-admiro::zone-styles :paths="$zoneStylesheets" />
        <x-admiro::scripts :files="['js/app-shell.js', 'js/toasts.js', 'js/input-mask.js', 'js/pin-keypad.js']" />
    </x-slot:head>

    {{ $before ?? '' }}

    @php($hasFooter = ! isset($brand))
    <div class="ss-footer-host"@if ($hasFooter) data-ss-footer-root="ss-footer-host"@endif>
    <div class="ss-shell ss-footer-stage" data-ss-shell>
        <aside class="ss-sidebar" id="ss-sidebar">
            <div class="ss-sidebar__header">
                @if ($account !== null)
                    @php($accountItems = $account['items'] ?? [])
                    @if ($accountItems !== [])
                        <details class="ss-account-menu ss-sidebar__account" data-ss-dropdown>
                            <summary class="ss-sidebar__identity" title="{{ $account['name'] }}">
                                <x-admiro::avatar :name="$account['name']" :image="$account['image'] ?? null" :color="$account['color'] ?? null" :accent="! isset($account['color'])" />
                                <span class="ss-sidebar__identity-text">
                                    <span class="ss-sidebar__identity-name">{{ $account['name'] }}</span>
                                    @if (filled($account['subtitle'] ?? null))
                                        <span class="ss-sidebar__identity-subtitle">{{ $account['subtitle'] }}</span>
                                    @endif
                                </span>
                                <span class="ss-sr-only">{{ $account['label'] ?? text('account.menu') }}</span>
                            </summary>
                            <div class="ss-account-menu__panel ss-sidebar__panel">
                                <ul class="ss-account-menu__list">
                                    @foreach ($accountItems as $item)
                                        <li><a class="ss-account-menu__link" href="{{ $item->url }}" @if ($item->current) aria-current="page" @endif><x-admiro::icon :name="$item->icon" class="ss-account-menu__icon" />{{ $item->label }}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                        </details>
                    @else
                        <div class="ss-sidebar__identity" title="{{ $account['name'] }}">
                            <x-admiro::avatar :name="$account['name']" :image="$account['image'] ?? null" :color="$account['color'] ?? null" :accent="! isset($account['color'])" />
                            <span class="ss-sidebar__identity-text">
                                <span class="ss-sidebar__identity-name">{{ $account['name'] }}</span>
                                @if (filled($account['subtitle'] ?? null))
                                    <span class="ss-sidebar__identity-subtitle">{{ $account['subtitle'] }}</span>
                                @endif
                            </span>
                        </div>
                    @endif
                @endif

                <div class="ss-sidebar__actions">
                    <button class="ss-icon-button ss-sidebar__collapse" type="button" aria-controls="ss-sidebar" aria-expanded="true" data-ss-sidebar-toggle>
                        <x-admiro::icon name="sidebar" class="ss-icon-button__icon" />
                        <span class="ss-sr-only">{{ text('layout.toggle_menu') }}</span>
                    </button>

                    @if ($contexts !== null && $contexts !== [])
                        <details class="ss-account-menu ss-context-switch" data-ss-dropdown>
                            <summary class="ss-icon-button ss-context-switch__toggle" title="{{ text('layout.switch_context') }}">
                                <x-admiro::icon name="switch" class="ss-icon-button__icon" />
                                <span class="ss-sr-only">{{ text('layout.switch_context') }}</span>
                            </summary>
                            <div class="ss-account-menu__panel ss-sidebar__panel">
                                @foreach ($contexts as $group)
                                    @if (filled($group->label))
                                        <p class="ss-context-switch__group">{{ $group->label }}</p>
                                    @endif
                                    <ul class="ss-account-menu__list">
                                        @foreach ($group->items as $item)
                                            <li>
                                                @if ($item instanceof \Simplysmart\Admiro\View\Navigation\NavigationForm)
                                                    <form method="POST" action="{{ $item->action }}">
                                                        @csrf
                                                        <input type="hidden" name="{{ $item->field }}" value="{{ $item->value }}">
                                                        <button class="ss-account-menu__link" type="submit" @if ($item->current) aria-current="true" @endif>
                                                            <x-admiro::icon :name="$item->icon" class="ss-account-menu__icon" />
                                                            <span>{{ $item->label }}@if ($item->current)<span class="ss-sr-only"> ({{ text('layout.current') }})</span>@endif</span>
                                                            @if ($item->current)
                                                                <x-admiro::icon name="check" class="ss-context-switch__check" />
                                                            @endif
                                                        </button>
                                                    </form>
                                                @else
                                                    <a class="ss-account-menu__link" href="{{ $item->url }}" @if ($item->current) aria-current="true" @endif>
                                                        <x-admiro::icon :name="$item->icon" class="ss-account-menu__icon" />
                                                        <span>{{ $item->label }}@if ($item->current)<span class="ss-sr-only"> ({{ text('layout.current') }})</span>@endif</span>
                                                        @if ($item->current)
                                                            <x-admiro::icon name="check" class="ss-context-switch__check" />
                                                        @endif
                                                    </a>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @endforeach
                            </div>
                        </details>
                    @endif

                    @if ($settings !== null)
                        <a class="ss-icon-button ss-sidebar__settings-toggle" href="{{ $settings->url }}" title="{{ text('layout.settings') }}" @if ($settings->active) aria-current="page" @endif>
                            <x-admiro::icon name="settings" class="ss-icon-button__icon" />
                            <span class="ss-sr-only">{{ text('layout.settings') }}</span>
                        </a>
                    @endif

                    @if ($notifications !== null)
                        @php($unread = $notifications->unreadCount())
                        <details class="ss-account-menu ss-notifications" data-ss-dropdown>
                            <summary class="ss-icon-button ss-notifications__toggle">
                                <x-admiro::icon name="bell" class="ss-icon-button__icon" />
                                <span class="ss-sr-only">{{ text('layout.notifications') }}@if ($unread > 0), {{ text('layout.notifications_unread', ['count' => $unread]) }}@endif</span>
                                @if ($unread > 0)
                                    <span class="ss-notifications__count" aria-hidden="true">{{ $unread }}</span>
                                @endif
                            </summary>
                            <div class="ss-account-menu__panel ss-sidebar__panel ss-notifications__panel">
                                <p class="ss-account-menu__name">{{ text('layout.notifications') }}</p>
                                @if ($notifications->items === [])
                                    <p class="ss-notifications__empty">{{ text('layout.notifications_empty') }}</p>
                                @else
                                    <ul class="ss-notifications__list">
                                        @foreach ($notifications->items as $item)
                                            <li><x-admiro::notification :item="$item" /></li>
                                        @endforeach
                                    </ul>
                                @endif
                                @if ($notifications->allUrl !== null)
                                    <a class="ss-notifications__all ss-link" href="{{ $notifications->allUrl }}">{{ text('layout.notifications_all') }}</a>
                                @endif
                            </div>
                        </details>
                    @endif
                </div>
            </div>

            <nav class="ss-sidebar__nav" aria-label="{{ $settings?->active ? text('layout.settings') : text('layout.menu') }}">
                @if ($settings?->active)
                    <a class="ss-sidebar__link ss-sidebar__back" href="{{ $settings->backUrl }}" title="{{ text('layout.back_to_app') }}">
                        <x-admiro::icon name="arrow-left" class="ss-sidebar__icon" />
                        <span class="ss-sidebar__label">{{ text('layout.back_to_app') }}</span>
                    </a>
                    <p class="ss-sidebar__title">{{ text('layout.settings') }}</p>
                @endif
                @foreach ($navigation as $group)
                    @if (filled($group->label))
                        <p class="ss-sidebar__group">{{ $group->label }}</p>
                    @endif
                    <ul class="ss-sidebar__list">
                        @foreach ($group->items as $item)
                            @if ($item->children !== [])
                                <li class="ss-sidebar__branch">
                                    <a class="ss-sidebar__link ss-sidebar__branch-link" href="{{ $item->url }}" title="{{ $item->label }}" @if ($item->isExpanded()) aria-current="page" @endif>
                                        <x-admiro::icon :name="$item->icon" class="ss-sidebar__icon" />
                                        <span class="ss-sidebar__label">{{ $item->label }}</span>
                                    </a>
                                    <details class="ss-sidebar__tree" @if ($item->isExpanded()) open @endif>
                                        <summary @class(['ss-sidebar__link', 'ss-sidebar__summary', 'ss-sidebar__summary--current' => $item->current]) title="{{ $item->label }}">
                                            <x-admiro::icon :name="$item->icon" class="ss-sidebar__icon" />
                                            <span class="ss-sidebar__label">{{ $item->label }}</span>
                                            @if (filled($item->badge))
                                                <span class="ss-sidebar__badge">{{ $item->badge }}</span>
                                            @endif
                                            <x-admiro::icon name="chevron-down" class="ss-sidebar__chevron" />
                                        </summary>
                                        <ul class="ss-sidebar__sublist">
                                            @foreach ($item->children as $child)
                                                <li>
                                                    <a class="ss-sidebar__sublink" href="{{ $child->url }}" @if ($child->current) aria-current="page" @endif>
                                                        <span class="ss-sidebar__label">{{ $child->label }}</span>
                                                        @if (filled($child->badge))
                                                            <span class="ss-sidebar__badge">{{ $child->badge }}</span>
                                                        @endif
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </details>
                                </li>
                            @else
                                <li>
                                    <a class="ss-sidebar__link" href="{{ $item->url }}" title="{{ $item->label }}" @if ($item->current) aria-current="page" @endif>
                                        <x-admiro::icon :name="$item->icon" class="ss-sidebar__icon" />
                                        <span class="ss-sidebar__label">{{ $item->label }}</span>
                                        @if (filled($item->badge))
                                            <span class="ss-sidebar__badge">{{ $item->badge }}</span>
                                        @endif
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                @endforeach
            </nav>

            @if ($signOut !== null)
                <form class="ss-sidebar__sign-out" method="POST" action="{{ $signOut }}">
                    @csrf
                    <button class="ss-sidebar__link" type="submit" title="{{ text('sign_in.sign_out') }}">
                        <x-admiro::icon name="log-out" class="ss-sidebar__icon" />
                        <span class="ss-sidebar__label">{{ text('sign_in.sign_out') }}</span>
                    </button>
                </form>
            @endif

            @isset($brand)
                <div class="ss-sidebar__brand">{{ $brand }}</div>
            @else
                <div class="ss-sidebar__brand">
                    <button class="ss-sidebar__brand-toggle" type="button" aria-expanded="false" aria-controls="ss-app-footer" title="{{ text('layout.footer_toggle') }}" data-ss-footer-toggle>
                        <x-admiro::logo class="ss-sidebar__logo" />
                        <span class="ss-sr-only">{{ text('layout.footer_toggle') }}</span>
                    </button>
                </div>
            @endisset
        </aside>

        <div class="ss-shell__backdrop" data-ss-sidebar-backdrop hidden></div>

        <div class="ss-shell__main">
            <header class="ss-topbar">
                <button class="ss-icon-button ss-topbar__menu" type="button" aria-controls="ss-sidebar" aria-expanded="false" data-ss-sidebar-toggle>
                    <x-admiro::icon name="sidebar" class="ss-icon-button__icon" />
                    <span class="ss-sr-only">{{ text('layout.toggle_menu') }}</span>
                </button>

                @if ($search !== null)
                    <form class="ss-search" role="search" method="GET" action="{{ $search->action }}">
                        <label class="ss-sr-only" for="ss-global-search">{{ text('layout.search') }}</label>
                        <x-admiro::icon name="search" class="ss-search__icon" />
                        <input class="ss-search__input" id="ss-global-search" name="{{ $search->name }}" type="search" value="{{ $search->value }}" placeholder="{{ $search->placeholder }}" autocomplete="off" aria-describedby="ss-global-search-hint" data-ss-search>
                        <kbd class="ss-search__kbd" id="ss-global-search-hint" title="{{ text('layout.search_shortcut') }}"><span aria-hidden="true">/</span><span class="ss-sr-only">{{ text('layout.search_shortcut') }}</span></kbd>
                    </form>
                @endif
            </header>

            <main class="ss-main">
                <x-admiro::app.context :title="$title" :meta="$meta" :back="$back">
                    @isset($actions)
                        <x-slot:actions>{{ $actions }}</x-slot:actions>
                    @endisset
                </x-admiro::app.context>

                {{ $slot }}
            </main>
        </div>
    </div>
    @if ($hasFooter)
        <x-admiro::brand-footer id="ss-app-footer" :legal-links="$legalLinks" media="(min-width: 64rem)" />
    @endif
    </div>

    <x-admiro::toasts :toasts="$toasts" />
</x-admiro::page>
