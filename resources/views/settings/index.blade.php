{{-- Ustawienia systemu. $countries, $currencies: Collection (aktywne), $languages: Collection<Language> (z APP_LOCALES), $country: ?Country, $currency: ?Currency, $language: string (kod języka domyślnego), $interface: list<string> (kody języków interfejsu), $canManage: bool. --}}
<x-mainboard::zone.layout :title="text('admiro::admin.settings_title')">
    @error('settings') <p role="alert">{{ $message }}</p> @enderror

    <form method="POST" action="{{ route('admiro.settings.update') }}">
        @csrf
        @method('PUT')
        <fieldset @disabled(! $canManage)>
            <label for="country">{{ text('admiro::admin.settings_country') }}</label>
            <select id="country" name="country">
                @foreach ($countries as $option)
                    <option value="{{ $option->alpha_code_2 }}" @selected($country?->is($option))>{{ $option->translatedName() }}</option>
                @endforeach
            </select>

            <label for="currency">{{ text('admiro::admin.settings_currency') }}</label>
            <select id="currency" name="currency">
                @foreach ($currencies as $option)
                    <option value="{{ $option->alpha_code_3 }}" @selected($currency?->is($option))>{{ $option->alpha_code_3 }} — {{ $option->translatedName() }}</option>
                @endforeach
            </select>

            <fieldset>
                <legend>{{ text('admiro::admin.settings_interface') }}</legend>
                @foreach ($languages as $option)
                    <label for="interface-{{ $option->code }}">
                        <input id="interface-{{ $option->code }}" name="interface[]" type="checkbox" value="{{ $option->code }}" @checked(in_array($option->code, $interface, true))>
                        {{ $option->native_name ?? $option->code }}
                    </label>
                @endforeach
            </fieldset>

            <label for="language">{{ text('admiro::admin.settings_language') }}</label>
            <select id="language" name="language">
                @foreach ($languages as $option)
                    <option value="{{ $option->code }}" @selected($language === $option->code)>{{ $option->native_name ?? $option->code }}</option>
                @endforeach
            </select>

            @if ($canManage)
                <button type="submit">{{ text_action('save') }}</button>
            @endif
        </fieldset>
    </form>
</x-mainboard::zone.layout>
