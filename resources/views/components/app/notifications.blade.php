{{-- Powiadomienia w layoucie aplikacji: dzwonek z licznikiem nieprzeczytanych (liczba także dla czytników ekranu) otwiera panel (<details data-ss-dropdown>) z listą (x-admiro::notification), komunikatem bez powiadomień i odnośnikiem do wszystkich. drawer: panel wysuwany przy krawędzi menu bocznego (.ss-notifications--drawer, wariant Workspace) zamiast menu rozwijanego. notifications: View\Notifications\NotificationPanel; toggleClass, iconClass, labelClass: klasy przycisku, ikony i etykiety (domyślnie tylko dla czytników ekranu; ss-icon-button__label — rozwijany opis). --}}
@props(['notifications', 'drawer' => false, 'toggleClass' => 'ss-icon-button', 'iconClass' => 'ss-icon-button__icon', 'labelClass' => 'ss-sr-only'])
@php($unread = $notifications->unreadCount())
<details @class(['ss-account-menu', 'ss-notifications', 'ss-notifications--drawer' => $drawer]) data-ss-dropdown>
    <summary class="{{ $toggleClass }} ss-notifications__toggle" @if ($labelClass === 'ss-sr-only') title="{{ text('layout.notifications') }}" @endif>
        <x-admiro::icon name="bell" :class="$iconClass" />
        <span class="{{ $labelClass }}">{{ text('layout.notifications') }}@if ($unread > 0), {{ text('layout.notifications_unread', ['count' => $unread]) }}@endif</span>
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
