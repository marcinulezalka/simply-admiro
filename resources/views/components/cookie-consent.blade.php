{{-- Zgoda na cookies (w szablonie bazowym x-mainboard::page, każda strona): tytuły z ikoną ciasteczka, przyciski z ikonami; baner na przyciemnionym tle (zezwól na wszystkie, odrzuć, dostosuj; odnośnik do polityki) i okno ustawień z kategoriami (niezbędne zawsze włączone, przełączniki pozostałych, lista cookies kategorii); skrypty opcjonalne nieaktywne do zgody. Logika w js/cookie-consent.js (ciasteczko zgody, rejestr zgód). $cookieConsent: ?Cookies\ConsentBannerState (null: baner niepotrzebny). --}}
@if ($cookieConsent !== null)
    <div class="ss-consent" data-ss-consent data-version="{{ $cookieConsent->version }}" data-endpoint="{{ $cookieConsent->consentUrl }}" data-cookie="{{ $cookieConsent->cookieName }}" data-days="{{ $cookieConsent->lifetimeDays }}">
        <dialog class="ss-consent__dialog ss-consent__banner" data-ss-consent-banner aria-labelledby="ss-consent-title" aria-describedby="ss-consent-lead">
            <h2 class="ss-consent__title" id="ss-consent-title"><x-mainboard::icon name="cookie" class="ss-consent__title-icon" />{{ text('cookies.banner.title') }}</h2>
            <p class="ss-consent__lead" id="ss-consent-lead">{{ text('cookies.banner.lead') }}</p>
            <div class="ss-consent__actions ss-consent__actions--stacked">
                <button class="ss-button ss-button--primary" type="button" data-ss-consent-action="accept"><x-mainboard::icon name="check-circle" class="ss-button__icon" />{{ text('cookies.actions.accept') }}</button>
                <button class="ss-button ss-button--primary" type="button" data-ss-consent-action="reject"><x-mainboard::icon name="close" class="ss-button__icon" />{{ text('cookies.actions.reject') }}</button>
                <button class="ss-button" type="button" data-ss-consent-action="customize"><x-mainboard::icon name="settings" class="ss-button__icon" />{{ text('cookies.actions.customize') }}</button>
            </div>
            @if ($cookieConsent->policyUrl !== null)
                <p class="ss-consent__links"><a class="ss-link" href="{{ $cookieConsent->policyUrl }}">{{ text('cookies.links.policy') }}</a></p>
            @endif
        </dialog>

        <dialog class="ss-consent__dialog ss-consent__settings" data-ss-consent-settings aria-labelledby="ss-consent-settings-title">
            <div class="ss-consent__header">
                <h2 class="ss-consent__title" id="ss-consent-settings-title"><x-mainboard::icon name="cookie" class="ss-consent__title-icon" />{{ text('cookies.settings.title') }}</h2>
                <button class="ss-icon-button" type="button" data-ss-consent-action="close" aria-label="{{ text('cookies.actions.close') }}"><x-mainboard::icon name="close" class="ss-icon-button__icon" /></button>
            </div>
            <p class="ss-consent__lead">{{ text('cookies.settings.lead') }}</p>
            <ul class="ss-consent__categories">
                @foreach (\Simplysmart\Mainboard\Enums\CookieCategory::cases() as $category)
                    @php($necessary = $category === \Simplysmart\Mainboard\Enums\CookieCategory::Necessary)
                    <li class="ss-consent__category">
                        <x-mainboard::switch :id="'ss-consent-' . $category->value" :label="$category->label()" :hint="$necessary ? text('cookies.settings.always_on') : $category->description()" :checked="$necessary" :disabled="$necessary" :value="$category->value" :data-ss-consent-category="$necessary ? null : $category->value" />
                        @if (($cookieConsent->cookies[$category->value] ?? []) !== [])
                            <details class="ss-consent__details">
                                <summary class="ss-link">{{ text('cookies.settings.show_cookies') }}</summary>
                                <dl class="ss-pairs">
                                    @foreach ($cookieConsent->cookies[$category->value] as $cookie)
                                        <div class="ss-pairs__row"><dt><code>{{ $cookie->name }}</code></dt><dd>{{ $cookie->purpose() }} · {{ $cookie->lifetime() }}</dd></div>
                                    @endforeach
                                </dl>
                            </details>
                        @endif
                    </li>
                @endforeach
            </ul>
            <div class="ss-consent__actions">
                <button class="ss-button ss-button--primary" type="button" data-ss-consent-action="accept"><x-mainboard::icon name="check-circle" class="ss-button__icon" />{{ text('cookies.actions.accept') }}</button>
                <button class="ss-button ss-button--primary" type="button" data-ss-consent-action="reject"><x-mainboard::icon name="close" class="ss-button__icon" />{{ text('cookies.actions.reject') }}</button>
                <button class="ss-button ss-consent__save" type="button" data-ss-consent-action="save"><x-mainboard::icon name="check" class="ss-button__icon" />{{ text('cookies.actions.save') }}</button>
            </div>
            @if ($cookieConsent->policyUrl !== null)
                <p class="ss-consent__links"><a class="ss-link" href="{{ $cookieConsent->policyUrl }}">{{ text('cookies.links.policy') }}</a></p>
            @endif
        </dialog>

        @foreach ($cookieConsent->scripts as $script)
            <script type="text/plain" data-ss-consent-script="{{ $script->category->value }}" data-src="{{ $script->src }}"></script>
        @endforeach
    </div>
    <x-mainboard::scripts :files="['js/cookie-consent.js']" />
@endif
