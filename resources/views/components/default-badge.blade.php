{{-- Plakietka wpisu domyślnego i wymaganego (np. organizacja operatora, język domyślny) przy jego nazwie: ikona ticket-slash (Hugeicons) i tekst, powód w podpowiedzi. label: tekst (np. „Domyślny”); title: powód (np. „Organizacja operatora — zawsze aktywna”). --}}
@props(['label', 'title' => null])
<span {{ $attributes->class(['ss-badge', 'ss-badge--outline', 'ss-badge--info', 'ss-badge--default']) }} @if (filled($title)) title="{{ $title }}" @endif><x-admiro::icon name="ticket-slash" class="ss-badge__icon" />{{ $label }}</span>
