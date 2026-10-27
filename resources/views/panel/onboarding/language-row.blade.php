{{-- Wiersz włączonego języka w panelu „Język aplikacji” zadania „Języki” kreatora onboardingu (panel/onboarding/show): flaga, nazwa (domyślny z plakietką „Domyślny”), nazwa własna i kod, siatka ustawień regionalnych (kraj z flagą, waluta, podstawowa stawka VAT z odnośnikiem do stawek kraju, dni wolne z odnośnikiem do kalendarza; brakujące w przerywanej ramce) — u języka domyślnego zapisane, a dopóki ustawienia regionalne nie są zapisane, propozycja według języka (RegionalSettingsSummary::forLanguage(), plakietka „Propozycja”) z przyciskiem „Zapisz jako domyślne” (onboarding.regional; tylko dla języków z APP_LOCALES); u pozostałych języków propozycja informacyjnie. Po prawej przełącznik ON / OFF zapisywany od razu (data-ss-autosubmit), a język domyślny — domyślny i wymagany — plakietkę „Domyślny” zamiast przełącznika (język interfejsu zablokowany). Z widoku nadrzędnego: $language: Language, $regionals: array<string, array{country: ?Country, currency: ?Currency, tax: ?TaxRate, holidays: int}>, $regionalConfigured: bool, $defaultCandidates: list<string>, $languageSwitch, $canManage, $zone. --}}
@php
    ['locked' => $locked, 'id' => $switchId] = $languageSwitch($language, 'enabled-language');
    $regional = $regionals[$language->code] ?? ['country' => null, 'currency' => null, 'tax' => null, 'holidays' => 0];
    $proposed = ! ($language->is_default && $regionalConfigured);
    $canSave = $canManage && $language->is_default && $proposed && $regional['country'] !== null && $regional['currency'] !== null && in_array($language->code, $defaultCandidates, true);
@endphp
<div @class(['ss-onboarding-languages__row', 'ss-onboarding-languages__row--default' => $language->is_default]) id="enabled-language-{{ $language->code }}-row">
    <span class="ss-onboarding-languages__main">
        <span class="ss-onboarding-picker__flags" aria-hidden="true">
            @foreach (array_slice($language->flagIcons(), 0, 1) as $flag)
                <x-admiro::flag :icon="$flag" />
            @endforeach
        </span>
        <span class="ss-onboarding-picker__chip-text">
            <span class="ss-onboarding-picker__name">{{ $language->translatedName() }}</span>
            <span class="ss-table__sub">{{ implode(' · ', array_filter([$language->native_name, $language->code])) }}</span>
        </span>
    </span>
    <dl @class(['ss-onboarding-languages__regional', 'ss-onboarding-languages__regional--proposed' => $proposed]) aria-label="{{ text('admiro::dashboard.onboarding_regional_title') }}">
        <div class="ss-onboarding-languages__regional-item">
            <dt>{{ text('admiro::dashboard.onboarding_regional_country') }}</dt>
            <dd>
                @if ($regional['country'] !== null)
                    @if ($regional['country']->flag_icon !== null)
                        <x-admiro::flag :icon="$regional['country']->flag_icon" />
                    @endif
                    {{ $regional['country']->translatedName() }}
                @else
                    —
                @endif
            </dd>
        </div>
        <div class="ss-onboarding-languages__regional-item">
            <dt>{{ text('admiro::dashboard.onboarding_regional_currency') }}</dt>
            <dd>{{ $regional['currency'] !== null ? $regional['currency']->alpha_code_3 . ' · ' . $regional['currency']->translatedName() : '—' }}</dd>
        </div>
        <div @class(['ss-onboarding-languages__regional-item', 'ss-onboarding-languages__regional-item--missing' => $regional['country'] !== null && $regional['tax'] === null])>
            <dt>{{ text('admiro::dashboard.onboarding_regional_taxes') }}</dt>
            <dd>
                @if ($regional['country'] === null)
                    —
                @else
                    <a class="ss-link" href="{{ route($zone . 'tax-rates.index', ['country' => $regional['country']->alpha_code_2]) }}">{{ $regional['tax'] !== null ? text('admiro::dashboard.onboarding_regional_vat', ['rate' => local_number((float) $regional['tax']->rate, 0)]) : text('admiro::dashboard.onboarding_regional_set') }}</a>
                @endif
            </dd>
        </div>
        <div @class(['ss-onboarding-languages__regional-item', 'ss-onboarding-languages__regional-item--missing' => $regional['country'] !== null && $regional['holidays'] === 0])>
            <dt>{{ text('admiro::dashboard.onboarding_regional_holidays') }}</dt>
            <dd>
                @if ($regional['country'] === null)
                    —
                @else
                    <a class="ss-link" href="{{ route($zone . 'holidays.index') }}">{{ $regional['holidays'] === 0 ? text('admiro::dashboard.onboarding_regional_set') : text('admiro::dashboard.onboarding_regional_holidays_count', ['count' => local_number($regional['holidays'])]) }}</a>
                @endif
            </dd>
        </div>
    </dl>
    <span class="ss-onboarding-languages__side">
        @if ($proposed && $language->is_default)
            <span class="ss-badge ss-badge--outline ss-badge--warning" title="{{ text('admiro::dashboard.onboarding_regional_proposed_hint') }}">{{ text('admiro::dashboard.onboarding_regional_proposed') }}</span>
        @endif
        @if ($canSave)
            <form method="POST" action="{{ route($zone . 'onboarding.regional') }}">
                @csrf
                <input type="hidden" name="language" value="{{ $language->code }}">
                <button class="ss-button ss-button--primary ss-button--sm" type="submit"><x-admiro::icon name="check" class="ss-button__icon" />{{ text('admiro::dashboard.onboarding_regional_save') }}</button>
            </form>
        @endif
        @if ($language->is_default)
            <span class="ss-badge ss-badge--outline ss-badge--info" title="{{ text('admiro::dashboard.onboarding_language_locked') }}">{{ text('admiro::dashboard.onboarding_language_default') }}</span>
        @elseif ($canManage)
            <form method="POST" action="{{ route($zone . 'dictionaries.toggle', ['dictionary' => 'languages', 'code' => $language->code]) }}" data-ss-autosubmit>
                @csrf
                <x-admiro::switch :id="$switchId" :label="text('admiro::dashboard.onboarding_language_switch', ['name' => $language->translatedName()])" :checked="true" :disabled="$locked" :title="$locked ? text('admiro::dashboard.onboarding_language_locked') : null" hide-label states />
            </form>
        @endif
    </span>
</div>
