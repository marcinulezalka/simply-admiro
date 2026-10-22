{{-- Pola pułapki formularza (Security\Honeypot\FormHoneypot), gdy `mainboard.honeypot.forms` jest włączone: ukryte pole dla botów i zaszyfrowany czas wyświetlenia. Bez stylów inline (CSP): pole ukrywa atrybut hidden. --}}
@php($honeypot = app(\Simplysmart\Mainboard\Security\Honeypot\FormHoneypot::class))
@if ($honeypot->isEnabled())
    <div hidden aria-hidden="true">
        <label for="{{ $honeypot::TRAP_FIELD }}">{{ text('sign_in.honeypot_field') }}</label>
        <input id="{{ $honeypot::TRAP_FIELD }}" name="{{ $honeypot::TRAP_FIELD }}" type="text" tabindex="-1" autocomplete="off">
    </div>
    <input type="hidden" name="{{ $honeypot::TIMESTAMP_FIELD }}" value="{{ $honeypot->token() }}">
@endif
