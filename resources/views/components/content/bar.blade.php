{{-- Pasek strony treści (x-mainboard::content.layout, nad i pod treścią): powrót (gdy back), odnośniki cookies (x-mainboard::cookie-links) i przełącznik motywu. back: ?string — adres powrotu. --}}
@props(['back' => null])
<div class="ss-content-page__bar">
    @if (filled($back))
        <a class="ss-link ss-content-page__back" href="{{ $back }}"><x-mainboard::icon name="arrow-left" class="ss-content-page__back-icon" />{{ text('layout.content.back') }}</a>
    @endif
    <x-mainboard::cookie-links class="ss-cookie-links ss-content-page__cookies" />
    <x-mainboard::theme-toggle class="ss-content-page__theme" />
</div>
