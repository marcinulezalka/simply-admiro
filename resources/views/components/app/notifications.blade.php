{{-- Powiadomienia w layoucie aplikacji: dzwonek z licznikiem nieprzeczytanych (liczba także dla czytników ekranu) otwiera panel (<details data-ss-dropdown>) z nagłówkiem (ikona dzwonka w kafelku, tytuł, zamknięcie) i przełącznikiem kategorii z ikonami (.ss-tabs--slider: wskaźnik przesuwa się na klikniętą kategorię, js/tabs.js; każda kategoria z nagłówkiem): powiadomienia (lista x-admiro::notification, komunikat bez powiadomień, odnośnik do wszystkich), skrzynka e-mail (miejsce na pocztę — integracja w przygotowaniu) i — gdy panel ma wpisy dziennika (auditItems) — dziennik zdarzeń (ostatnie wpisy i odnośnik do pełnego dziennika). Bez skryptu wszystkie kategorie są widoczne jedna pod drugą. notifications: View\Notifications\NotificationPanel; toggleClass, iconClass, labelClass: klasy przycisku, ikony i etykiety (domyślnie tylko dla czytników ekranu). right: panel wysuwany z prawej krawędzi okna na wysokość obszaru treści, między górą okna a stopką (przycisk w stopce Workspace, jak kalendarz). --}}
@props(['notifications', 'right' => false, 'toggleClass' => 'ss-icon-button', 'iconClass' => 'ss-icon-button__icon', 'labelClass' => 'ss-sr-only'])
@php
    $unread = $notifications->unreadCount();
@endphp
<details @class(['ss-account-menu', 'ss-notifications', 'ss-notifications--right' => $right]) data-ss-dropdown>
    <summary class="{{ $toggleClass }} ss-notifications__toggle" @if ($labelClass === 'ss-sr-only') title="{{ text('layout.notifications') }}" @endif>
        <x-admiro::icon name="bell" :class="$iconClass" />
        <span class="{{ $labelClass }}">{{ text('layout.notifications') }}@if ($unread > 0), {{ text('layout.notifications_unread', ['count' => $unread]) }}@endif</span>
        @if ($unread > 0)
            <span class="ss-notifications__count" aria-hidden="true">{{ $unread }}</span>
        @endif
    </summary>
    <div class="ss-account-menu__panel ss-sidebar__panel ss-notifications__panel">
        <div class="ss-notifications__header">
            <x-admiro::icon name="bell" class="ss-icon--tile" />
            <h2 class="ss-notifications__title">{{ text('layout.notifications') }}</h2>
            <button class="ss-icon-button" type="button" title="{{ text('layout.close') }}" data-ss-dropdown-close>
                <x-admiro::icon name="close" class="ss-icon-button__icon" />
                <span class="ss-sr-only">{{ text('layout.close') }}</span>
            </button>
        </div>
        <x-admiro::app.notification-tabs :notifications="$notifications" />
    </div>
</details>
