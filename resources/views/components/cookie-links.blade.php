{{-- Odnośniki cookies w stopkach layoutów (logowanie, strony treści, menu konta): „Ustawienia cookies” otwiera okno ustawień (js/cookie-consent.js), „Polityka cookies” prowadzi do strony polityki; nic, gdy baner jest niepotrzebny. class: klasa kontenera; parts: które odnośniki (`settings`, `policy`, domyślnie oba), np. gdy stopka rozdziela je na dwa miejsca; icon: ikona `settings` (suwaki) przed „Ustawienia cookies”. $cookieConsent: ?Cookies\ConsentBannerState. --}}
@props(['class' => 'ss-cookie-links', 'parts' => ['settings', 'policy'], 'icon' => false])
@if ($cookieConsent !== null && ($parts !== ['policy'] || $cookieConsent->policyUrl !== null))
    <p class="{{ $class }}">
        @if (in_array('settings', $parts, true))
            <button class="ss-link ss-cookie-links__button" type="button" data-ss-cookie-settings>@if ($icon)<x-mainboard::icon name="settings" class="ss-cookie-links__icon" />@endif{{ text('cookies.links.settings') }}</button>
        @endif
        @if (in_array('policy', $parts, true) && $cookieConsent->policyUrl !== null)
            <a class="ss-link" href="{{ $cookieConsent->policyUrl }}">{{ text('cookies.links.policy') }}</a>
        @endif
    </p>
@endif
