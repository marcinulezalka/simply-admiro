{{-- Panel boczny stopki layoutu Workspace (przycisk-zegar: ikona kalendarza, duża godzina krojem mono i mniejsza data „27.10.2026”, odświeżane co minutę; z powiadomieniami — liczba nieprzeczytanych): otwiera z prawej krawędzi okna panel (<details data-ss-dropdown>, js/calendar-panel.js) z sekcjami obok siebie w dwóch kolumnach (na wąskim ekranie jedna pod drugą), rozsuwanymi razem, a zwijanymi osobno (<details open>): powiadomienia (notifications: ?View\Notifications\NotificationPanel — zakładki x-admiro::app.notification-tabs z komunikatami systemowymi toasts: ?list<View\Toast>) i kalendarz (miesiąc od poniedziałku, dziś wyróżnione, dni wolne z kropką, podgląd wybranego dnia — x-admiro::app.month —, miejsce na skrzynkę pocztową); na dole trzy podpisane wiersze ikon x-admiro::app.appearance-tools (js/theme.js, bez skryptu ukryte): ustawienia (koło zębate, settingsUrl: ?string), tryby kolorów (jasny/ciemny, motywy wysokiego kontrastu WCAG) oraz rozmiar i powiększenie (A, AA, pomniejsz −, powiększ + — o jeden stopień rozmiaru tekstu); zawartość przycisku-zegara daje x-admiro::app.clock. calendar: View\Calendar\CalendarMonth. --}}
@props(['calendar', 'notifications' => null, 'toasts' => null, 'settingsUrl' => null])
@php
    $unread = $notifications?->unreadCount() ?? 0;
@endphp
<details class="ss-calendar-panel" data-ss-dropdown>
    <summary class="ss-calendar-panel__toggle" data-ss-clock>
        <x-admiro::app.clock :unread="$unread" />
    </summary>
    <aside class="ss-calendar-panel__drawer ss-side-panel" aria-label="{{ text('layout.calendar') }}" data-ss-calendar>
        <div class="ss-side-panel__close">
            <button class="ss-icon-button" type="button" title="{{ text('layout.close') }}" data-ss-calendar-close hidden>
                <x-admiro::icon name="close" class="ss-icon-button__icon" />
                <span class="ss-sr-only">{{ text('layout.close') }}</span>
            </button>
        </div>
        <div @class(['ss-side-panel__columns', 'ss-side-panel__columns--two' => $notifications !== null])>
            @if ($notifications !== null)
                <details class="ss-side-panel__section" open>
                    <summary class="ss-side-panel__summary"><x-admiro::icon name="bell" class="ss-side-panel__summary-icon" /><span class="ss-side-panel__summary-title">{{ text('layout.notifications') }}</span><x-admiro::icon name="chevron-down" class="ss-side-panel__chevron" /></summary>
                    <x-admiro::app.notification-tabs :notifications="$notifications" :toasts="$toasts" />
                </details>
            @endif
            <details class="ss-side-panel__section" open>
                <summary class="ss-side-panel__summary"><x-admiro::icon name="calendar" class="ss-side-panel__summary-icon" /><span class="ss-side-panel__summary-title">{{ ucfirst(local_month($calendar->month, 'MMMM')) }} {{ $calendar->month->year }}</span><x-admiro::icon name="chevron-down" class="ss-side-panel__chevron" /></summary>
                <div class="ss-side-panel__body">
                    <x-admiro::app.month :calendar="$calendar" />
                    @if ($notifications === null)
                    <section class="ss-calendar-panel__mail">
                        <h3 class="ss-calendar-panel__heading"><x-admiro::icon name="mail" class="ss-calendar-panel__heading-icon" />{{ text('layout.mailbox') }}</h3>
                        <p class="ss-muted">{{ text('layout.mailbox_lead') }}</p>
                        <button class="ss-button ss-button--sm" type="button" disabled>{{ text('layout.mailbox_connect') }}</button>
                        <ul class="ss-calendar-panel__messages">
                            <li class="ss-calendar-panel__message ss-muted">{{ text('layout.mailbox_empty') }}</li>
                        </ul>
                    </section>
                    @endif
                </div>
            </details>
        </div>
        <x-admiro::app.appearance-tools :settings-url="$settingsUrl" />
    </aside>
</details>
