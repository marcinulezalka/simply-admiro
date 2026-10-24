{{-- Odnośniki cookies w stopkach layoutów (logowanie, strony treści, menu konta): „Ustawienia cookies” otwiera okno ustawień (js/cookie-consent.js), „Polityka cookies” prowadzi do strony polityki; nic, gdy baner jest niepotrzebny. class: klasa kontenera. $cookieConsent: ?Cookies\ConsentBannerState. --}}
@props(['class' => 'ss-cookie-links'])
@if ($cookieConsent !== null)
    <p class="{{ $class }}">
        <button class="ss-link ss-cookie-links__button" type="button" data-ss-cookie-settings>{{ text('cookies.links.settings') }}</button>
        @if ($cookieConsent->policyUrl !== null)
            <a class="ss-link" href="{{ $cookieConsent->policyUrl }}">{{ text('cookies.links.policy') }}</a>
        @endif
    </p>
@endif
