{{-- Karta obracana (.ss-flip, js/flip-card.js): przód (slot) i tył w jednej komórce siatki — wysokość większej z nich —; tył ma odwrót główny (slot back) i opcjonalny drugi (slot alternate), każdy z własnym przyciskiem w prawym górnym rogu: icon (domyślnie rotate-left, REICON) z podpisem label obraca kartę na odwrót główny, alternateIcon (domyślnie flag) z podpisem alternateLabel — na drugi, stojący przy nim po lewej. Zjechanie kursorem z karty, Esc albo przycisk „Wróć” na odwrocie (backLabel) obraca ją z powrotem. Strona niewidoczna jest wyłączona z nawigacji (inert, aria-hidden). Bez skryptu widać tylko przód, a przyciski są ukryte. --}}
@props(['label', 'backLabel' => null, 'icon' => 'rotate-left', 'alternateLabel' => null, 'alternateIcon' => 'flag'])
<div {{ $attributes->class(['ss-flip']) }} data-ss-flip>
    <div class="ss-flip__inner">
        <div class="ss-flip__face ss-flip__face--front" data-ss-flip-front>{{ $slot }}</div>
        <div class="ss-flip__face ss-flip__face--back" data-ss-flip-back inert aria-hidden="true">
            <div class="ss-flip__side" data-ss-flip-side="back">{{ $back }}</div>
            @isset($alternate)
                <div class="ss-flip__side" data-ss-flip-side="alternate" hidden>{{ $alternate }}</div>
            @endisset
            <button class="ss-flip__return ss-link" type="button" data-ss-flip-return><x-admiro::icon name="arrow-left" class="ss-flip__return-icon" />{{ $backLabel ?? text_action('back') }}</button>
        </div>
    </div>
    <span class="ss-flip__toggles">
        @isset($alternate)
            <button class="ss-icon-button ss-flip__toggle" type="button" aria-expanded="false" title="{{ $alternateLabel }}" data-ss-flip-toggle="alternate" hidden><x-admiro::icon :name="$alternateIcon" class="ss-icon-button__icon" /><span class="ss-sr-only">{{ $alternateLabel }}</span></button>
        @endisset
        <button class="ss-icon-button ss-flip__toggle" type="button" aria-expanded="false" title="{{ $label }}" data-ss-flip-toggle="back" hidden><x-admiro::icon :name="$icon" class="ss-icon-button__icon" /><span class="ss-sr-only">{{ $label }}</span></button>
    </span>
</div>
