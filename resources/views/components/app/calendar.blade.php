{{-- Data i godzina w stopce layoutu Workspace z kalendarzem: przycisk-panel o szerokości treści: ikona kalendarza i obok pełna data z godziną („wtorek, 27 paź 2026, 10:42”), bez rozsuwania; odświeżana skryptem co minutę otwiera panel wysuwany z prawej krawędzi okna (<details data-ss-dropdown>, js/calendar-panel.js) z miesiącem (tydzień od poniedziałku, dziś wyróżnione, dni wolne aplikacji z kropką), podglądem wybranego dnia (data i dzień wolny albo brak wydarzeń; klik w dzień zmienia podgląd), zamknięcie w nagłówku panelu i miejscem na skrzynkę pocztową (podłączenie i lista wiadomości — integracja w przygotowaniu). calendar: View\Calendar\CalendarMonth. --}}
@props(['calendar'])
@php($todayHoliday = $calendar->holiday($calendar->today))
<details class="ss-calendar-panel" data-ss-dropdown>
    <summary class="ss-calendar-panel__toggle" data-ss-clock>
        @php($now = now())
        <x-admiro::icon name="calendar" class="ss-calendar-panel__icon" />
        <time class="ss-calendar-panel__date" datetime="{{ $now->format('Y-m-d\TH:i') }}" data-ss-clock-date>{{ local_date($now, 'dddd, D MMM YYYY') }}, {{ $now->format('H:i') }}</time>
        <span class="ss-sr-only">{{ text('layout.calendar') }}</span>
    </summary>
    <aside class="ss-calendar-panel__drawer" aria-label="{{ text('layout.calendar') }}" data-ss-calendar>
        <div class="ss-calendar-panel__header">
            <h2 class="ss-calendar-panel__title">{{ ucfirst(local_month($calendar->month, 'MMMM')) }} {{ $calendar->month->year }}</h2>
            <button class="ss-icon-button" type="button" title="{{ text('layout.close') }}" data-ss-calendar-close hidden>
                <x-admiro::icon name="close" class="ss-icon-button__icon" />
                <span class="ss-sr-only">{{ text('layout.close') }}</span>
            </button>
        </div>
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
        <section class="ss-calendar-panel__mail">
            <h3 class="ss-calendar-panel__heading"><x-admiro::icon name="mail" class="ss-calendar-panel__heading-icon" />{{ text('layout.mailbox') }}</h3>
            <p class="ss-muted">{{ text('layout.mailbox_lead') }}</p>
            <button class="ss-button ss-button--sm" type="button" disabled>{{ text('layout.mailbox_connect') }}</button>
            <ul class="ss-calendar-panel__messages">
                <li class="ss-calendar-panel__message ss-muted">{{ text('layout.mailbox_empty') }}</li>
            </ul>
        </section>
    </aside>
</details>
