{{-- Zawartość przycisku-zegara w stopce (<summary> panelu bocznego: x-admiro::app.calendar, x-admiro::app.side-panels): ikona kalendarza, duża godzina krojem mono i mniejsza data „27.10.2026” (odświeżane co minutę, js/calendar-panel.js — przycisk ma data-ss-clock), etykieta dla czytników ekranu i liczba nieprzeczytanych powiadomień (unread: int, 0 = bez licznika). --}}
@props(['unread' => 0])
@php($now = now())
<x-admiro::icon name="calendar" class="ss-calendar-panel__icon" />
<time class="ss-calendar-panel__date" datetime="{{ $now->format('Y-m-d\TH:i') }}" data-ss-clock-date><span class="ss-calendar-panel__time" data-ss-clock-time>{{ $now->format('H:i') }}</span><span class="ss-calendar-panel__day" data-ss-clock-day>{{ $now->format('d.m.Y') }}</span></time>
<span class="ss-sr-only">{{ text('layout.calendar') }}@if ($unread > 0), {{ text('layout.notifications_unread', ['count' => $unread]) }}@endif</span>
@if ($unread > 0)
    <span class="ss-notifications__count" aria-hidden="true">{{ $unread }}</span>
@endif
