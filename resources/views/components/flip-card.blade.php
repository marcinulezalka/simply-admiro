{{-- Karta obracana (.ss-flip, js/flip-card.js): przód (slot) i tył (slot back) w jednej komórce siatki — wysokość większej z nich —; przycisk w prawym górnym rogu (icon — domyślnie rotate-left, REICON —, podpis label) obraca kartę w 3D na drugą stronę, a zjechanie kursorem z karty, Esc albo przycisk „Wróć” na odwrocie (backLabel) obraca ją z powrotem. Strona niewidoczna jest wyłączona z nawigacji (inert, aria-hidden). Bez skryptu widać tylko przód, a przycisk jest ukryty. --}}
@props(['label', 'backLabel' => null, 'icon' => 'rotate-left'])
<div {{ $attributes->class(['ss-flip']) }} data-ss-flip>
    <div class="ss-flip__inner">
        <div class="ss-flip__face ss-flip__face--front" data-ss-flip-front>{{ $slot }}</div>
        <div class="ss-flip__face ss-flip__face--back" data-ss-flip-back inert aria-hidden="true">
            {{ $back }}
            <button class="ss-flip__return ss-link" type="button" data-ss-flip-toggle><x-admiro::icon name="arrow-left" class="ss-flip__return-icon" />{{ $backLabel ?? text_action('back') }}</button>
        </div>
    </div>
    <button class="ss-icon-button ss-flip__toggle" type="button" aria-expanded="false" title="{{ $label }}" data-ss-flip-toggle hidden><x-admiro::icon :name="$icon" class="ss-icon-button__icon" /><span class="ss-sr-only">{{ $label }}</span></button>
</div>
