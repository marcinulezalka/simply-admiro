{{-- Ustawienia systemu: zakładki ustawień i słowników, kraj, waluta, języki interfejsu (przełączniki) i język domyślny na karcie formularza; tylko do odczytu bez settings.manage. $countries, $currencies: Collection (aktywne), $languages: Collection<Language> (z APP_LOCALES), $country: ?Country, $currency: ?Currency, $language: string (kod języka domyślnego), $interface: list<string> (kody języków interfejsu), $canManage: bool. --}}
<x-mainboard::zone.layout :title="text('admiro::admin.settings_title')">
    @include('admiro::panel.dictionaries.tabs', ['current' => null])

    @error('settings') <p class="ss-notice" role="alert"><x-mainboard::icon name="alert-triangle" /> {{ $message }}</p> @enderror

    <form class="ss-card" method="POST" action="{{ route('admiro.settings.update') }}">
        @csrf
        @method('PUT')
        <fieldset class="ss-form ss-fieldset" @disabled(! $canManage)>
            <div class="ss-form-row">
                <div class="ss-field ss-form-row__grow">
                    <label class="ss-field__label" for="country">{{ text('admiro::admin.settings_country') }}</label>
                    <select class="ss-input" id="country" name="country">
                        @foreach ($countries as $option)
                            <option value="{{ $option->alpha_code_2 }}" @selected($country?->is($option))>{{ $option->translatedName() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ss-field ss-form-row__grow">
                    <label class="ss-field__label" for="currency">{{ text('admiro::admin.settings_currency') }}</label>
                    <select class="ss-input" id="currency" name="currency">
                        @foreach ($currencies as $option)
                            <option value="{{ $option->alpha_code_3 }}" @selected($currency?->is($option))>{{ $option->alpha_code_3 }} — {{ $option->translatedName() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <fieldset class="ss-field ss-fieldset">
                <legend class="ss-field__label">{{ text('admiro::admin.settings_interface') }}</legend>
                @foreach ($languages as $option)
                    <x-mainboard::switch :id="'interface-' . $option->code" name="interface[]" :value="$option->code" :label="$option->native_name ?? $option->code" :checked="in_array($option->code, $interface, true)" />
                @endforeach
            </fieldset>
            <div class="ss-field">
                <label class="ss-field__label" for="language">{{ text('admiro::admin.settings_language') }}</label>
                <select class="ss-input" id="language" name="language">
                    @foreach ($languages as $option)
                        <option value="{{ $option->code }}" @selected($language === $option->code)>{{ $option->native_name ?? $option->code }}</option>
                    @endforeach
                </select>
            </div>
            @if ($canManage)
                <div class="ss-form-actions">
                    <button class="ss-button ss-button--primary" type="submit"><x-mainboard::icon name="check" class="ss-button__icon" />{{ text_action('save') }}</button>
                </div>
            @endif
        </fieldset>
    </form>
</x-mainboard::zone.layout>
