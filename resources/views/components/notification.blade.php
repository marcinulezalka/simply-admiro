{{-- Jedno powiadomienie (panel w top barze i lista powiadomień): ikona, tytuł, treść, czas; nieprzeczytane wyróżnione kropką i tekstem dla czytników ekranu. item: View\Notifications\NotificationItem. --}}
@props(['item'])
@php($tag = $item->url !== null ? 'a' : 'div')
<{{ $tag }} @class(['ss-notification', 'ss-notification--unread' => $item->unread]) @if ($item->url !== null) href="{{ $item->url }}" @endif>
    <x-mainboard::icon :name="$item->icon" class="ss-notification__icon" />
    <span class="ss-notification__text">
        <span class="ss-notification__title">{{ $item->title }}@if ($item->unread)<span class="ss-sr-only"> ({{ text('layout.unread') }})</span>@endif</span>
        @if (filled($item->body))
            <span class="ss-notification__body">{{ $item->body }}</span>
        @endif
        <span class="ss-notification__time">{{ $item->time }}</span>
    </span>
</{{ $tag }}>
