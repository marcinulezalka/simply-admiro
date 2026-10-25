{{-- Podpis odręczny (js/signature-pad.js): pole rysowania (canvas) z etykietą, ukryte pole z obrazem PNG (data URL) i przycisk „Wyczyść”; zapisany podpis jest odtwarzany. Skrypt dołączany raz na stronę (z nonce CSP). id, name, label: wymagane; value: zapisany podpis (data URL) albo null; error: tekst błędu. --}}
@props(['id', 'name', 'label', 'value' => null, 'error' => null])
<div {{ $attributes->merge(['class' => 'ss-field ss-signature']) }} data-ss-signature-pad>
    <label class="ss-field__label" for="{{ $id }}">{{ $label }}</label>
    <canvas class="ss-signature__canvas" id="{{ $id }}" width="480" height="160" role="img" aria-label="{{ $label }}" data-ss-signature></canvas>
    <input type="hidden" name="{{ $name }}" value="{{ $value }}" data-ss-signature-value>
    <p class="ss-field__hint">{{ text('layout.signature.hint') }}</p>
    <noscript><p class="ss-field__hint">{{ text('layout.signature.noscript') }}</p></noscript>
    <button class="ss-button" type="button" data-ss-signature-clear hidden>{{ text('layout.signature.clear') }}</button>
    @if (filled($error))
        <p class="ss-field__error" role="alert">{{ $error }}</p>
    @endif
</div>
@once
    <x-mainboard::scripts :files="['js/signature-pad.js']" />
@endonce
