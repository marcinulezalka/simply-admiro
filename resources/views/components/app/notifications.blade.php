{{-- Powiadomienia w layoucie aplikacji: dzwonek z licznikiem nieprzeczytanych (liczba także dla czytników ekranu) otwiera panel (<details data-ss-dropdown>) z nagłówkiem (ikona dzwonka w kafelku, tytuł, zamknięcie) i przełącznikiem kategorii z ikonami (.ss-tabs--slider: wskaźnik przesuwa się na klikniętą kategorię, js/tabs.js; każda kategoria z nagłówkiem): powiadomienia (lista x-admiro::notification, komunikat bez powiadomień, odnośnik do wszystkich), skrzynka e-mail (miejsce na pocztę — integracja w przygotowaniu) i — gdy panel ma wpisy dziennika (auditItems) — dziennik zdarzeń (ostatnie wpisy i odnośnik do pełnego dziennika). Bez skryptu wszystkie kategorie są widoczne jedna pod drugą. notifications: View\Notifications\NotificationPanel; toggleClass, iconClass, labelClass: klasy przycisku, ikony i etykiety (domyślnie tylko dla czytników ekranu). right: panel wysuwany z prawej krawędzi okna na wysokość obszaru treści, między górą okna a stopką (przycisk w stopce Workspace, jak kalendarz). --}}
@props(['notifications', 'right' => false, 'toggleClass' => 'ss-icon-button', 'iconClass' => 'ss-icon-button__icon', 'labelClass' => 'ss-sr-only'])
@php
    $unread = $notifications->unreadCount();
    $tabs = array_filter([
        'notifications' => ['icon' => 'bell', 'label' => text('layout.notifications')],
        'mailbox' => ['icon' => 'mail', 'label' => text('layout.mailbox')],
        'audit' => $notifications->auditItems === null ? null : ['icon' => 'history', 'label' => text('layout.audit_log')],
    ]);
@endphp
<details @class(['ss-account-menu', 'ss-notifications', 'ss-notifications--right' => $right]) data-ss-dropdown>
    <summary class="{{ $toggleClass }} ss-notifications__toggle" @if ($labelClass === 'ss-sr-only') title="{{ text('layout.notifications') }}" @endif>
        <x-admiro::icon name="bell" :class="$iconClass" />
        <span class="{{ $labelClass }}">{{ text('layout.notifications') }}@if ($unread > 0), {{ text('layout.notifications_unread', ['count' => $unread]) }}@endif</span>
        @if ($unread > 0)
            <span class="ss-notifications__count" aria-hidden="true">{{ $unread }}</span>
        @endif
    </summary>
    <div class="ss-account-menu__panel ss-sidebar__panel ss-notifications__panel" data-ss-tabs>
        <div class="ss-notifications__header">
            <x-admiro::icon name="bell" class="ss-icon--tile" />
            <h2 class="ss-notifications__title">{{ text('layout.notifications') }}</h2>
            <button class="ss-icon-button" type="button" title="{{ text('layout.close') }}" data-ss-dropdown-close>
                <x-admiro::icon name="close" class="ss-icon-button__icon" />
                <span class="ss-sr-only">{{ text('layout.close') }}</span>
            </button>
        </div>
        <div class="ss-tabs ss-tabs--slider ss-notifications__tabs" role="tablist" aria-label="{{ text('layout.notifications') }}" hidden data-ss-tabs-list>
            @foreach ($tabs as $key => $tab)
                <button class="ss-tabs__link" type="button" role="tab" id="ss-notifications-tab-{{ $key }}" title="{{ $tab['label'] }}" aria-controls="ss-notifications-{{ $key }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" data-ss-tab>
                    <x-admiro::icon :name="$tab['icon']" class="ss-tabs__icon" /><span class="ss-sr-only">{{ $tab['label'] }}</span>
                </button>
            @endforeach
        </div>

        <section class="ss-notifications__section" id="ss-notifications-notifications" role="tabpanel" aria-labelledby="ss-notifications-tab-notifications" data-ss-tab-panel>
            <h3 class="ss-notifications__heading">{{ $tabs['notifications']['label'] }}</h3>
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
        </section>

        <section class="ss-notifications__section" id="ss-notifications-mailbox" role="tabpanel" aria-labelledby="ss-notifications-tab-mailbox" data-ss-tab-panel>
            <h3 class="ss-notifications__heading">{{ $tabs['mailbox']['label'] }}</h3>
            <p class="ss-notifications__lead ss-muted">{{ text('layout.mailbox_lead') }}</p>
            <button class="ss-button ss-button--sm" type="button" disabled>{{ text('layout.mailbox_connect') }}</button>
            <p class="ss-notifications__empty">{{ text('layout.mailbox_empty') }}</p>
        </section>

        @if ($notifications->auditItems !== null)
            <section class="ss-notifications__section" id="ss-notifications-audit" role="tabpanel" aria-labelledby="ss-notifications-tab-audit" data-ss-tab-panel>
                <h3 class="ss-notifications__heading">{{ $tabs['audit']['label'] }}</h3>
                @if ($notifications->auditItems === [])
                    <p class="ss-notifications__empty">{{ text('layout.audit_log_empty') }}</p>
                @else
                    <ul class="ss-notifications__list">
                        @foreach ($notifications->auditItems as $item)
                            <li><x-admiro::notification :item="$item" /></li>
                        @endforeach
                    </ul>
                @endif
                @if ($notifications->auditUrl !== null)
                    <a class="ss-notifications__all ss-link" href="{{ $notifications->auditUrl }}">{{ text('layout.audit_log_all') }}</a>
                @endif
            </section>
        @endif
    </div>
</details>
