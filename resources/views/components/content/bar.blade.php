{{-- Pasek strony treści (x-mainboard::content.layout, nad i pod treścią): powrót (gdy back), i odnośniki cookies (x-mainboard::cookie-links); bez przełącznika motywu (obowiązuje motyw systemu albo zapisany wybór). back: ?string — adres powrotu. --}}
@props(['back' => null])
<div class="ss-content-page__bar">
    @if (filled($back))
        <a class="ss-link ss-content-page__back" href="{{ $back }}"><x-mainboard::icon name="arrow-left" class="ss-content-page__back-icon" />{{ text('layout.content.back') }}</a>
    @endif
    <x-mainboard::cookie-links class="ss-cookie-links ss-content-page__cookies" />
</div>
