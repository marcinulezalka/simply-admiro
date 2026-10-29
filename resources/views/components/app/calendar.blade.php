{{-- Panel boczny stopki layoutu Workspace (przycisk-zegar: ikona kalendarza, duża godzina krojem mono i mniejsza data „27.10.2026”, odświeżane co minutę; z powiadomieniami — liczba nieprzeczytanych): otwiera z prawej krawędzi okna panel (<details data-ss-dropdown>, js/calendar-panel.js) z sekcjami jedna pod drugą, rozsuwanymi razem, a zwijanymi osobno (<details open>): powiadomienia (notifications: ?View\Notifications\NotificationPanel — zakładki x-admiro::app.notification-tabs z komunikatami systemowymi toasts: ?list<View\Toast>) i kalendarz (miesiąc od poniedziałku, dziś wyróżnione, dni wolne z kropką, podgląd wybranego dnia, miejsce na skrzynkę pocztową); na dole pasek ikon wyglądu (js/theme.js, bez skryptu ukryty): tryb jasny/ciemny, motywy wysokiego kontrastu WCAG, rozmiar tekstu A / AA, powiększenie (bardzo duży tekst) i koło zębate do ustawień (settingsUrl: ?string). calendar: View\Calendar\CalendarMonth. --}}
@props(['calendar', 'notifications' => null, 'toasts' => null, 'settingsUrl' => null])
@php
    $todayHoliday = $calendar->holiday($calendar->today);
    $unread = $notifications?->unreadCount() ?? 0;
@endphp
<details class="ss-calendar-panel" data-ss-dropdown>
    <summary class="ss-calendar-panel__toggle" data-ss-clock>
        @php($now = now())
        <x-admiro::icon name="calendar" class="ss-calendar-panel__icon" />
        <time class="ss-calendar-panel__date" datetime="{{ $now->format('Y-m-d\TH:i') }}" data-ss-clock-date><span class="ss-calendar-panel__time" data-ss-clock-time>{{ $now->format('H:i') }}</span><span class="ss-calendar-panel__day" data-ss-clock-day>{{ $now->format('d.m.Y') }}</span></time>
        <span class="ss-sr-only">{{ text('layout.calendar') }}@if ($unread > 0), {{ text('layout.notifications_unread', ['count' => $unread]) }}@endif</span>
        @if ($unread > 0)
            <span class="ss-notifications__count" aria-hidden="true">{{ $unread }}</span>
        @endif
    </summary>
    <aside class="ss-calendar-panel__drawer ss-side-panel" aria-label="{{ text('layout.calendar') }}" data-ss-calendar>
        <div class="ss-side-panel__close">
            <button class="ss-icon-button" type="button" title="{{ text('layout.close') }}" data-ss-calendar-close hidden>
                <x-admiro::icon name="close" class="ss-icon-button__icon" />
                <span class="ss-sr-only">{{ text('layout.close') }}</span>
            </button>
        </div>
        @if ($notifications !== null)
            <details class="ss-side-panel__section" open>
                <summary class="ss-side-panel__summary"><x-admiro::icon name="bell" class="ss-side-panel__summary-icon" /><span class="ss-side-panel__summary-title">{{ text('layout.notifications') }}</span><x-admiro::icon name="chevron-down" class="ss-side-panel__chevron" /></summary>
                <x-admiro::app.notification-tabs :notifications="$notifications" :toasts="$toasts" />
            </details>
        @endif
        <details class="ss-side-panel__section" open>
            <summary class="ss-side-panel__summary"><x-admiro::icon name="calendar" class="ss-side-panel__summary-icon" /><span class="ss-side-panel__summary-title">{{ ucfirst(local_month($calendar->month, 'MMMM')) }} {{ $calendar->month->year }}</span><x-admiro::icon name="chevron-down" class="ss-side-panel__chevron" /></summary>
            <div class="ss-side-panel__body">
                <div class="ss-mini-calendar" role="group" aria-label="{{ text('layout.calendar') }}">
                    @foreach (range(1, 7) as $weekday)
                        <span class="ss-mini-calendar__weekday">{{ local_day($weekday, 'dd') }}</span>
                    @endforeach
                    @foreach ($calendar->weeks() as $week)
                        @foreach ($week as $day)
                            @php($holiday = $calendar->holiday($day))
                            <button @class(['ss-mini-calendar__day', 'ss-mini-calendar__day--outside' => ! $calendar->isInMonth($day), 'ss-mini-calendar__day--today' => $calendar->isToday($day), 'ss-mini-calendar__day--holiday' => $holiday !== null]) type="button" aria-pressed="{{ $calendar->isToday($day) ? 'true' : 'false' }}" @if ($calendar->isToday($day)) aria-current="date" @endif data-ss-calendar-day data-label="{{ local_date($day, 'dddd, D MMMM YYYY') }}" data-note="{{ $holiday ?? text('layout.calendar_empty') }}">{{ $day->day }}</button>
                        @endforeach
                    @endforeach
                </div>
                <section class="ss-calendar-panel__preview" aria-live="polite">
                    <h3 class="ss-calendar-panel__heading" data-ss-calendar-label>{{ ucfirst(local_date($calendar->today, 'dddd, D MMMM YYYY')) }}</h3>
                    <p class="ss-calendar-panel__note" data-ss-calendar-note>{{ $todayHoliday ?? text('layout.calendar_empty') }}</p>
                </section>
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
        <div class="ss-side-panel__tools" role="group" aria-label="{{ text('layout.appearance_tools') }}">
            <span class="ss-side-panel__tool-group" data-ss-theme-flip-control hidden>
                <button class="ss-icon-button ss-side-panel__tool" type="button" aria-pressed="false" title="{{ text('layout.theme_dark') }}" data-ss-theme-flip><x-admiro::icon name="moon" class="ss-icon-button__icon" /><span class="ss-sr-only">{{ text('layout.theme_dark') }}</span></button>
            </span>
            <span class="ss-side-panel__tool-group" data-ss-theme-toggle hidden>
                <button class="ss-icon-button ss-side-panel__tool" type="button" aria-pressed="false" title="{{ text('layout.themes.contrast') }}" data-ss-theme="contrast"><x-admiro::icon name="contrast" class="ss-icon-button__icon" /><span class="ss-sr-only">{{ text('layout.themes.contrast') }}</span></button>
                <button class="ss-icon-button ss-side-panel__tool" type="button" aria-pressed="false" title="{{ text('layout.themes.contrast-yellow') }}" data-ss-theme="contrast-yellow"><x-admiro::icon name="eye" class="ss-icon-button__icon" /><span class="ss-sr-only">{{ text('layout.themes.contrast-yellow') }}</span></button>
            </span>
            <span class="ss-side-panel__tool-group" data-ss-preference="text-size" hidden>
                <button class="ss-icon-button ss-side-panel__tool ss-side-panel__tool--text" type="button" aria-pressed="false" title="{{ text('layout.text_sizes.normal') }}" data-ss-preference-value="normal">A<span class="ss-sr-only"> — {{ text('layout.text_sizes.normal') }}</span></button>
                <button class="ss-icon-button ss-side-panel__tool ss-side-panel__tool--text" type="button" aria-pressed="false" title="{{ text('layout.text_sizes.large') }}" data-ss-preference-value="large">AA<span class="ss-sr-only"> — {{ text('layout.text_sizes.large') }}</span></button>
                <button class="ss-icon-button ss-side-panel__tool" type="button" aria-pressed="false" title="{{ text('layout.text_sizes.larger') }}" data-ss-preference-value="larger"><x-admiro::icon name="zoom-in" class="ss-icon-button__icon" /><span class="ss-sr-only">{{ text('layout.text_sizes.larger') }}</span></button>
            </span>
            @if ($settingsUrl !== null)
                <a class="ss-icon-button ss-side-panel__tool ss-side-panel__settings" href="{{ $settingsUrl }}" title="{{ text('layout.settings') }}"><x-admiro::icon name="settings-gear" class="ss-icon-button__icon" /><span class="ss-sr-only">{{ text('layout.settings') }}</span></a>
            @endif
        </div>
    </aside>
</details>
