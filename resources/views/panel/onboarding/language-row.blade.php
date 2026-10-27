{{-- Wiersz włączonego języka w zadaniu „Języki” kreatora onboardingu (panel/onboarding/show): flaga, nazwa, nazwa własna i kod, przełącznik zapisywany od razu (data-ss-autosubmit; język interfejsu zablokowany), a u języka domyślnego — domyślnego i wymaganego — zamiast przełącznika plakietka „Domyślny”. Z widoku nadrzędnego: $language: Language, $languageSwitch, $canManage, $zone. --}}
@php(['locked' => $locked, 'id' => $switchId] = $languageSwitch($language, 'enabled-language'))
<div class="ss-onboarding-languages__row">
    <span class="ss-onboarding-picker__flags" aria-hidden="true">
        @foreach (array_slice($language->flagIcons(), 0, 1) as $flag)
            <x-admiro::flag :icon="$flag" />
        @endforeach
    </span>
    <span class="ss-onboarding-picker__chip-text">
        <span class="ss-onboarding-picker__name">{{ $language->translatedName() }}</span>
        <span class="ss-table__sub">{{ implode(' · ', array_filter([$language->native_name, $language->code])) }}</span>
    </span>
    @if ($language->is_default)
        <span class="ss-badge ss-badge--outline ss-badge--info" title="{{ text('admiro::dashboard.onboarding_language_locked') }}">{{ text('admiro::dashboard.onboarding_language_default') }}</span>
    @elseif ($canManage)
        <form method="POST" action="{{ route($zone . 'dictionaries.toggle', ['dictionary' => 'languages', 'code' => $language->code]) }}" data-ss-autosubmit>
            @csrf
            <x-admiro::switch :id="$switchId" :label="text('admiro::dashboard.onboarding_language_switch', ['name' => $language->translatedName()])" :checked="true" :disabled="$locked" :title="$locked ? text('admiro::dashboard.onboarding_language_locked') : null" hide-label />
        </form>
    @endif
</div>
