{{-- Miesiąc kalendarza panelu bocznego (x-admiro::app.calendar, x-admiro::app.side-panels): siatka od poniedziałku (dziś wyróżnione, dni wolne z kropką; klik w dzień pokazuje go w podglądzie, js/calendar-panel.js) i podgląd wybranego dnia (domyślnie dziś: dzień wolny albo brak wydarzeń). calendar: View\Calendar\CalendarMonth. --}}
@props(['calendar'])
@php($todayHoliday = $calendar->holiday($calendar->today))
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
