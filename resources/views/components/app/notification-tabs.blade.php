{{-- Zakładki panelu powiadomień (x-admiro::app.notifications i panel boczny kalendarza x-admiro::app.calendar): przełącznik kategorii z ikonami (.ss-tabs--slider, js/tabs.js) — powiadomienia, komunikaty systemowe (toasts: toasty bieżącej strony, gdy podane), skrzynka e-mail i dziennik zdarzeń (gdy panel ma auditItems) — i ich listy; bez skryptu kategorie jedna pod drugą. notifications: View\Notifications\NotificationPanel; toasts: ?list<View\Toast> (null = bez zakładki systemowej). --}}
@props(['notifications', 'toasts' => null])
@php
    $tabs = array_filter([
        'notifications' => ['icon' => 'bell', 'label' => text('layout.notifications')],
        'system' => $toasts === null ? null : ['icon' => 'info', 'label' => text('layout.system_messages')],
        'mailbox' => ['icon' => 'mail', 'label' => text('layout.mailbox')],
        'audit' => $notifications->auditItems === null ? null : ['icon' => 'history', 'label' => text('layout.audit_log')],
    ]);
@endphp
<div class="ss-notifications__tabs-wrap" data-ss-tabs>
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

    @if ($toasts !== null)
        <section class="ss-notifications__section" id="ss-notifications-system" role="tabpanel" aria-labelledby="ss-notifications-tab-system" data-ss-tab-panel>
            <h3 class="ss-notifications__heading">{{ $tabs['system']['label'] }}</h3>
            @if ($toasts === [])
                <p class="ss-notifications__empty">{{ text('layout.system_messages_empty') }}</p>
            @else
                <ul class="ss-notifications__list">
                    @foreach ($toasts as $toast)
                        <li class="ss-notifications__system ss-notifications__system--{{ $toast->type->value }}"><strong>{{ $toast->heading() }}</strong> {{ $toast->message }}</li>
                    @endforeach
                </ul>
            @endif
        </section>
    @endif

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
