{{-- Layout aplikacji z menu bocznym (docs/design-system.md §5). Menu po lewej: u góry awatar z imieniem i nazwiskiem (albo nazwą) i drugą linią (np. organizacja, rola) — kliknięcie otwiera menu konta; pod nim zwijanie menu, przełącznik kontekstu i dzwonek powiadomień; pozycje menu; na dole subtelne logo. Menu zwija się do ikon (stan w przeglądarce), na telefonie wysuwa się z boku (przycisk w top barze). Top bar: globalna wyszukiwarka (skrót „/”). navigation: list<View\Navigation\NavigationGroup>; search: ?View\Navigation\SearchBox; account: ?array{name: string, subtitle?: string, image?: string, color?: Enums\AvatarColor, items?: list<View\Navigation\NavigationItem>} (bez koloru awatar w kolorze akcentu); contexts: ?list<View\Navigation\NavigationGroup> (przełącznik kontekstu, np. widok i organizacja; bieżący przez current); notifications: ?View\Notifications\NotificationPanel; toasts: list<View\Toast> albo null = z sesji (Toast::SESSION_KEY); stylesheets: dodatkowe arkusze z public/assets/ po arkuszach Core; sloty: actions (akcje nagłówka), before (pasek nad aplikacją, np. podgląd w design systemie), brand (zamiast logo w stopce menu). Menu rozwijane działają bez skryptu (<details>). --}}
@props(['title', 'meta' => null, 'back' => null, 'navigation' => [], 'search' => null, 'account' => null, 'contexts' => null, 'notifications' => null, 'toasts' => null, 'description' => null, 'stylesheets' => []])
@php($toasts ??= \Simplysmart\Mainboard\View\Toast::fromSession(session(\Simplysmart\Mainboard\View\Toast::SESSION_KEY)))
<x-mainboard::page :title="$title" :description="$description" robots="noindex, nofollow">
    <x-slot:head>
        <x-mainboard::styles :files="[...\Simplysmart\Mainboard\View\CoreAssets::STYLESHEETS, ...$stylesheets]" />
        <x-mainboard::scripts :files="['js/app-shell.js', 'js/toasts.js', 'js/input-mask.js']" />
    </x-slot:head>

    {{ $before ?? '' }}

    <div class="ss-shell" data-ss-shell>
        <aside class="ss-sidebar" id="ss-sidebar">
            <div class="ss-sidebar__header">
                @if ($account !== null)
                    @php($accountItems = $account['items'] ?? [])
                    @if ($accountItems !== [])
                        <details class="ss-account-menu ss-sidebar__account" data-ss-dropdown>
                            <summary class="ss-sidebar__identity" title="{{ $account['name'] }}">
                                <x-mainboard::avatar :name="$account['name']" :image="$account['image'] ?? null" :color="$account['color'] ?? null" :accent="! isset($account['color'])" />
                                <span class="ss-sidebar__identity-text">
                                    <span class="ss-sidebar__identity-name">{{ $account['name'] }}</span>
                                    @if (filled($account['subtitle'] ?? null))
                                        <span class="ss-sidebar__identity-subtitle">{{ $account['subtitle'] }}</span>
                                    @endif
                                </span>
                                <span class="ss-sr-only">{{ text('account.menu') }}</span>
                            </summary>
                            <div class="ss-account-menu__panel ss-sidebar__panel">
                                <ul class="ss-account-menu__list">
                                    @foreach ($accountItems as $item)
                                        <li><a class="ss-account-menu__link" href="{{ $item->url }}" @if ($item->current) aria-current="page" @endif><x-mainboard::icon :name="$item->icon" class="ss-account-menu__icon" />{{ $item->label }}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                        </details>
                    @else
                        <div class="ss-sidebar__identity" title="{{ $account['name'] }}">
                            <x-mainboard::avatar :name="$account['name']" :image="$account['image'] ?? null" :color="$account['color'] ?? null" :accent="! isset($account['color'])" />
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
                        <x-mainboard::icon name="sidebar" class="ss-icon-button__icon" />
                        <span class="ss-sr-only">{{ text('layout.toggle_menu') }}</span>
                    </button>

                    @if ($contexts !== null && $contexts !== [])
                        <details class="ss-account-menu ss-context-switch" data-ss-dropdown>
                            <summary class="ss-icon-button ss-context-switch__toggle" title="{{ text('layout.switch_context') }}">
                                <x-mainboard::icon name="switch" class="ss-icon-button__icon" />
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
                                                <a class="ss-account-menu__link" href="{{ $item->url }}" @if ($item->current) aria-current="true" @endif>
                                                    <x-mainboard::icon :name="$item->icon" class="ss-account-menu__icon" />
                                                    <span>{{ $item->label }}@if ($item->current)<span class="ss-sr-only"> ({{ text('layout.current') }})</span>@endif</span>
                                                    @if ($item->current)
                                                        <x-mainboard::icon name="check" class="ss-context-switch__check" />
                                                    @endif
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endforeach
                            </div>
                        </details>
                    @endif

                    @if ($notifications !== null)
                        @php($unread = $notifications->unreadCount())
                        <details class="ss-account-menu ss-notifications" data-ss-dropdown>
                            <summary class="ss-icon-button ss-notifications__toggle">
                                <x-mainboard::icon name="bell" class="ss-icon-button__icon" />
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
                                            <li><x-mainboard::notification :item="$item" /></li>
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

            <nav class="ss-sidebar__nav" aria-label="{{ text('layout.menu') }}">
                @foreach ($navigation as $group)
                    @if (filled($group->label))
                        <p class="ss-sidebar__group">{{ $group->label }}</p>
                    @endif
                    <ul class="ss-sidebar__list">
                        @foreach ($group->items as $item)
                            <li>
                                <a class="ss-sidebar__link" href="{{ $item->url }}" title="{{ $item->label }}" @if ($item->current) aria-current="page" @endif>
                                    <x-mainboard::icon :name="$item->icon" class="ss-sidebar__icon" />
                                    <span class="ss-sidebar__label">{{ $item->label }}</span>
                                    @if (filled($item->badge))
                                        <span class="ss-sidebar__badge">{{ $item->badge }}</span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endforeach
            </nav>

            <div class="ss-sidebar__brand">
                @isset($brand)
                    {{ $brand }}
                @else
                    <x-mainboard::logo class="ss-sidebar__logo" />
                @endisset
            </div>
        </aside>

        <div class="ss-shell__backdrop" data-ss-sidebar-backdrop hidden></div>

        <div class="ss-shell__main">
            <header class="ss-topbar">
                <button class="ss-icon-button ss-topbar__menu" type="button" aria-controls="ss-sidebar" aria-expanded="false" data-ss-sidebar-toggle>
                    <x-mainboard::icon name="sidebar" class="ss-icon-button__icon" />
                    <span class="ss-sr-only">{{ text('layout.toggle_menu') }}</span>
                </button>

                @if ($search !== null)
                    <form class="ss-search" role="search" method="GET" action="{{ $search->action }}">
                        <label class="ss-sr-only" for="ss-global-search">{{ text('layout.search') }}</label>
                        <x-mainboard::icon name="search" class="ss-search__icon" />
                        <input class="ss-search__input" id="ss-global-search" name="{{ $search->name }}" type="search" value="{{ $search->value }}" placeholder="{{ $search->placeholder }}" autocomplete="off" aria-describedby="ss-global-search-hint" data-ss-search>
                        <kbd class="ss-search__kbd" id="ss-global-search-hint" title="{{ text('layout.search_shortcut') }}"><span aria-hidden="true">/</span><span class="ss-sr-only">{{ text('layout.search_shortcut') }}</span></kbd>
                    </form>
                @endif
            </header>

            <main class="ss-main">
                <x-mainboard::app.context :title="$title" :meta="$meta" :back="$back">
                    @isset($actions)
                        <x-slot:actions>{{ $actions }}</x-slot:actions>
                    @endisset
                </x-mainboard::app.context>

                {{ $slot }}
            </main>
        </div>
    </div>

    <x-mainboard::toasts :toasts="$toasts" />
</x-mainboard::page>
