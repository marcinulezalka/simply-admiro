{{-- Wiersz wpisu w zadaniach słownikowych kreatora onboardingu (panel/onboarding/show, Panel\Onboarding\DictionarySteps), na wzór wiersza języka: flaga albo kod, nazwa, kody, siatka faktów zależnie od zadania i po prawej akcje. Kraje — waluta, UE, dni wolne w roku, podstawowy VAT; przełącznik z gałką ON / OFF (dictionaries.toggle, od razu; wpis domyślny ma w tym miejscu wypełnioną ikonę x-admiro::default-badge). Waluty — ostatni kurs NBP (kurs, data, tabela; bez kursu „Brak kursu”); przełącznik jak w krajach. Kalendarz — dni wolne w roku i najbliższy dzień wolny; „Pobierz święta” (holidays.sync dla kraju i roku, wraca do kreatora) i odnośnik do kalendarza. Stawki podatkowe — stawki w mocy dziś (podstawowa pierwsza, brak — przerywana ramka) i odnośnik do stawek kraju. Z widoku nadrzędnego: $entry: Country|Currency, $facts: array<string, array>, $current: OnboardingStep, $year: int, $canManage: bool, $prefix: string, $zone. --}}
@php
    $isCurrency = $entry instanceof \Simplysmart\Admiro\Models\Dictionaries\Currency;
    $code = $isCurrency ? $entry->alpha_code_3 : $entry->alpha_code_2;
    $fact = $facts[$code] ?? [];
    $step = $current;
    $dictionary = $isCurrency ? 'currencies' : 'countries';
    $switchable = in_array($step, [\Simplysmart\Admiro\Panel\Enums\OnboardingStep::Countries, \Simplysmart\Admiro\Panel\Enums\OnboardingStep::Currencies], true);
@endphp
<li @class(['ss-onboarding-languages__row', 'ss-onboarding-languages__row--default' => $entry->is_default === true]) id="{{ $isCurrency ? 'currency' : 'country' }}-{{ $code }}">
    <span class="ss-onboarding-languages__main">
        @if ($isCurrency)
            <span class="ss-onboarding-dictionary__code">{{ $code }}</span>
        @else
            <span class="ss-onboarding-picker__flags" aria-hidden="true">
                @if ($entry->flag_icon !== null)
                    <x-admiro::flag :icon="$entry->flag_icon" />
                @endif
            </span>
        @endif
        <span class="ss-onboarding-picker__chip-text">
            <span class="ss-onboarding-picker__name">{{ $entry->translatedName() }}</span>
            <span class="ss-table__sub">{{ $isCurrency ? implode(' · ', array_filter([$entry->alpha_code_3, $entry->numeric_code])) : implode(' · ', array_filter([$entry->alpha_code_2, $entry->alpha_code_3, $entry->dial_code !== null ? '+' . ltrim($entry->dial_code, '+') : null])) }}</span>
        </span>
    </span>
    <dl class="ss-onboarding-languages__regional">
        @if ($step === \Simplysmart\Admiro\Panel\Enums\OnboardingStep::Currencies)
            <div @class(['ss-onboarding-languages__regional-item', 'ss-onboarding-languages__regional-item--missing' => ($fact['rate'] ?? null) === null && $entry->is_default !== true])>
                <dt>{{ text('admiro::dashboard.onboarding_currency_rate') }}</dt>
                <dd>
                    @if ($entry->is_default === true)
                        {{ text('admiro::dashboard.onboarding_currency_base') }}
                    @elseif (($fact['rate'] ?? null) !== null)
                        {{ local_number((float) $fact['rate']->rate, 4) }} · {{ local_date($fact['rate']->rate_date, 'D MMM YYYY') }}
                    @else
                        {{ text('admiro::dashboard.onboarding_currency_no_rate') }}
                    @endif
                </dd>
            </div>
            <div class="ss-onboarding-languages__regional-item">
                <dt>{{ text('admiro::dashboard.onboarding_currency_table') }}</dt>
                <dd>{{ ($fact['rate'] ?? null)?->table_number ?? '—' }}</dd>
            </div>
        @elseif ($step === \Simplysmart\Admiro\Panel\Enums\OnboardingStep::Countries)
            <div class="ss-onboarding-languages__regional-item">
                <dt>{{ text('admiro::dashboard.onboarding_regional_currency') }}</dt>
                <dd>{{ ($fact['currency'] ?? null) !== null ? $fact['currency']->alpha_code_3 . ' · ' . $fact['currency']->translatedName() : '—' }}</dd>
            </div>
            <div class="ss-onboarding-languages__regional-item">
                <dt>{{ text('admiro::dashboard.onboarding_country_eu') }}</dt>
                <dd>{{ $entry->is_eu ? text('admiro::admin.yes') : text('admiro::admin.no') }}</dd>
            </div>
            <div class="ss-onboarding-languages__regional-item">
                <dt>{{ text('admiro::dashboard.onboarding_regional_holidays') }}</dt>
                <dd>{{ local_number($fact['holidays'] ?? 0) }}</dd>
            </div>
            <div class="ss-onboarding-languages__regional-item">
                <dt>{{ text('admiro::dashboard.onboarding_regional_taxes') }}</dt>
                <dd>{{ ($fact['tax'] ?? null) !== null ? text('admiro::dashboard.onboarding_regional_vat', ['rate' => local_number((float) $fact['tax']->rate, 0)]) : '—' }}</dd>
            </div>
        @elseif ($step === \Simplysmart\Admiro\Panel\Enums\OnboardingStep::Calendar)
            <div @class(['ss-onboarding-languages__regional-item', 'ss-onboarding-languages__regional-item--missing' => ($fact['holidays'] ?? 0) === 0])>
                <dt>{{ text('admiro::dashboard.onboarding_calendar_days_off', ['year' => $year]) }}</dt>
                <dd>{{ local_number($fact['holidays'] ?? 0) }}</dd>
            </div>
            <div class="ss-onboarding-languages__regional-item">
                <dt>{{ text('admiro::dashboard.onboarding_calendar_next') }}</dt>
                <dd>{{ ($fact['next'] ?? null) !== null ? local_date($fact['next']->holiday_date, 'D MMM') . ' · ' . $fact['next']->translatedName() : '—' }}</dd>
            </div>
        @else
            <div @class(['ss-onboarding-languages__regional-item', 'ss-onboarding-languages__regional-item--missing' => ($fact['tax'] ?? null) === null])>
                <dt>{{ text('admiro::dashboard.onboarding_taxes_standard') }}</dt>
                <dd>{{ ($fact['tax'] ?? null) !== null ? local_number((float) $fact['tax']->rate, 0) . '%' : '—' }}</dd>
            </div>
            <div class="ss-onboarding-languages__regional-item ss-onboarding-dictionary__wide">
                <dt>{{ text('admiro::dashboard.onboarding_taxes_others') }}</dt>
                <dd>
                    @php($others = ($fact['taxes'] ?? collect())->reject(static fn ($rate): bool => $rate->type === \Simplysmart\Admiro\Enums\TaxRateType::Standard))
                    @forelse ($others as $rate)
                        <span class="ss-badge ss-badge--outline">{{ $rate->type->label() }}{{ $rate->rate !== null ? ' ' . local_number((float) $rate->rate, 0) . '%' : '' }}</span>
                    @empty
                        —
                    @endforelse
                </dd>
            </div>
        @endif
    </dl>
    <span class="ss-onboarding-languages__side">
        @if ($step === \Simplysmart\Admiro\Panel\Enums\OnboardingStep::Calendar)
            @if ($canManage)
                <form method="POST" action="{{ route($zone . 'holidays.sync') }}">
                    @csrf
                    <input type="hidden" name="country" value="{{ $code }}">
                    <input type="hidden" name="year" value="{{ $year }}">
                    <input type="hidden" name="onboarding" value="{{ $step->value }}">
                    <button class="ss-button ss-button--sm" type="submit"><x-admiro::icon name="refresh" class="ss-button__icon" />{{ text('admiro::dashboard.onboarding_calendar_sync', ['year' => $year]) }}</button>
                </form>
            @endif
            <a class="ss-link" href="{{ route($zone . 'holidays.index', ['country' => $code, 'year' => $year]) }}">{{ text('admiro::dashboard.onboarding_calendar_open') }}</a>
        @elseif ($step === \Simplysmart\Admiro\Panel\Enums\OnboardingStep::Taxes)
            <a class="ss-link" href="{{ route($zone . 'tax-rates.index', ['country' => $code]) }}">{{ text('admiro::dashboard.onboarding_taxes_open') }}</a>
        @elseif ($switchable && $entry->is_default === true)
            <x-admiro::default-badge :label="text('admiro::dashboard.onboarding_dictionary_default')" :title="text('admiro::dashboard.onboarding_dictionary_default_hint')" />
        @elseif ($switchable && $canManage)
            <form method="POST" action="{{ route($zone . 'dictionaries.toggle', ['dictionary' => $dictionary, 'code' => $code]) }}" data-ss-autosubmit>
                @csrf
                <x-admiro::switch :id="$prefix . '-' . $code" :label="text('admiro::dashboard.onboarding_dictionary_switch', ['name' => $entry->translatedName()])" :checked="$entry->is_active === true" hide-label knob />
            </form>
        @endif
    </span>
</li>
