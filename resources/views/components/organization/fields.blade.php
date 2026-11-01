{{-- Pola danych organizacji (Organizations\OrganizationForm) w trzech kartach w kolejności: dane organizacji (nazwa, nazwa skrócona, NIP z maską, REGON, KRS; slot na dodatkowe pola, np. organizację nadrzędną), dane adresowe z krajem (flaga kraju jako ikona w polu, x-admiro::flag-select; w nowym wpisie kraj domyślny aplikacji; Organizations\ContactCountries) i dane kontaktowe (telefon stacjonarny i komórkowy — każdy w osobnym wierszu: prefiks kraju z flagą z listy aktywnych krajów i numer, .ss-phone; e-mail, e-mail do rozliczeń). Nagłówek każdej karty z ikoną; każde pole w osobnym wierszu; bez przypisów z formatem (format narzuca maska pola). Bez przycisku i znacznika <form>. organization: ?Organization (null = nowa); disabled: bool (tylko do odczytu); Etykiety z text_data(Organization), wartości z old() albo z organizacji. --}}
@props(['organization' => null, 'disabled' => false])
@php
    $model = \Simplysmart\Admiro\Models\Organization::class;
    $value = static fn (string $field): mixed => old($field, $organization?->getAttribute($field));
    $label = static fn (string $field): string => text_data($model, $field);
    $length = \Simplysmart\Admiro\Models\Organization::DETAIL_LENGTHS;
    $contactCountries = app(\Simplysmart\Admiro\Organizations\ContactCountries::class);
    $countries = $contactCountries->countries();
    $countryCode = $contactCountries->selectedCode($organization);
    $phones = array_map(static function (string $column) use ($contactCountries, $organization, $countryCode): array {
        $split = $contactCountries->split($organization?->getAttribute($column), $countryCode);
        $prefix = \Simplysmart\Admiro\Organizations\ContactCountries::prefixField($column);

        return ['column' => $column, 'prefix' => $prefix, 'country' => old($prefix, $split['country']), 'number' => old($column, $split['number'])];
    }, \Simplysmart\Admiro\Models\Organization::PHONES);
    $dialCountries = $countries->filter(static fn ($country): bool => filled($country->dial_code));
    $flagsOf = static fn ($list): array => $list->mapWithKeys(static fn ($country): array => [$country->alpha_code_2 => $country->flag_icon])->all();
    $flags = $flagsOf($countries);
    $dialFlags = $flagsOf($dialCountries);
    $selectedCountry = old('country_code', $countryCode);
@endphp
<section class="ss-card" aria-labelledby="organization-details-title">
    <h2 class="ss-card__title ss-card__title--icon" id="organization-details-title"><x-admiro::icon name="building" class="ss-card__title-icon" />{{ text('layout.organization_form.details') }}</h2>
    <fieldset class="ss-form ss-fieldset" @disabled($disabled)>
        <x-admiro::input id="name" name="name" :label="$label('name')" icon="building" :value="$value('name')" :placeholder="text('placeholders.company')" :error="$errors->first('name')" required maxlength="255" />
        <x-admiro::input id="short_name" name="short_name" :label="$label('short_name')" :hint="text('layout.organization_form.short_name_hint')" :value="$value('short_name')" :error="$errors->first('short_name')" :maxlength="\Simplysmart\Admiro\Models\Organization::SHORT_NAME_MAX_LENGTH" />
        <x-admiro::input id="nip" name="nip" :label="$label('nip')" :mask="\Simplysmart\Admiro\Enums\InputMask::TaxId" :value="$value('nip')" :error="$errors->first('nip')" autocomplete="off" />
        <x-admiro::input id="regon" name="regon" :label="$label('regon')" :value="$value('regon')" :error="$errors->first('regon')" :maxlength="$length['regon']" inputmode="numeric" autocomplete="off" />
        <x-admiro::input id="krs" name="krs" :label="$label('krs')" :value="$value('krs')" :error="$errors->first('krs')" :maxlength="$length['krs']" inputmode="numeric" autocomplete="off" />
        {{ $slot }}
    </fieldset>
</section>

<section class="ss-card" aria-labelledby="organization-address-title">
    <h2 class="ss-card__title ss-card__title--icon" id="organization-address-title"><x-admiro::icon name="map-pin" class="ss-card__title-icon" />{{ text('layout.organization_form.address') }}</h2>
    <fieldset class="ss-form ss-fieldset" @disabled($disabled)>
        <x-admiro::input id="street" name="street" :label="$label('street')" icon="map-pin" :value="$value('street')" :error="$errors->first('street')" :maxlength="$length['street']" />
        <x-admiro::input id="postal_code" name="postal_code" :label="$label('postal_code')" :mask="\Simplysmart\Admiro\Enums\InputMask::PostalCode" :value="$value('postal_code')" :error="$errors->first('postal_code')" />
        <x-admiro::input id="city" name="city" :label="$label('city')" :value="$value('city')" :error="$errors->first('city')" :maxlength="$length['city']" />
        <div class="ss-field">
            <label class="ss-field__label" for="country_code">{{ $label('country_code') }}</label>
            <x-admiro::flag-select :flags="$flags" :selected="$selectedCountry" id="country_code" name="country_code" :aria-invalid="$errors->has('country_code') ? 'true' : null">
                <option value="" @selected($selectedCountry === null)>—</option>
                @foreach ($countries as $country)
                    <option value="{{ $country->alpha_code_2 }}" @selected($selectedCountry === $country->alpha_code_2)>{{ $country->translatedName() }}</option>
                @endforeach
            </x-admiro::flag-select>
            @error('country_code') <p class="ss-field__error" role="alert">{{ $message }}</p> @enderror
        </div>
    </fieldset>
</section>

<section class="ss-card" aria-labelledby="organization-contact-title">
    <h2 class="ss-card__title ss-card__title--icon" id="organization-contact-title"><x-admiro::icon name="phone" class="ss-card__title-icon" />{{ text('layout.organization_form.contact') }}</h2>
    <fieldset class="ss-form ss-fieldset" @disabled($disabled)>
        @foreach ($phones as $phone)
            <div class="ss-phone">
                <x-admiro::flag-select :flags="$dialFlags" :selected="$phone['country']" class="ss-phone__prefix" :id="$phone['prefix']" :name="$phone['prefix']" :aria-label="$label($phone['column']) . ' — ' . text('layout.organization_form.phone_prefix')">
                    @foreach ($dialCountries as $country)
                        <option value="{{ $country->alpha_code_2 }}" @selected($phone['country'] === $country->alpha_code_2)>{{ $contactCountries->dialCode($country->alpha_code_2) }}</option>
                    @endforeach
                </x-admiro::flag-select>
                <x-admiro::input :id="$phone['column']" :name="$phone['column']" type="tel" field-class="ss-phone__number" :label="$label($phone['column'])" :icon="$phone['column'] === \Simplysmart\Admiro\Models\Organization::MOBILE_PHONE ? 'smartphone' : 'phone'" :value="$phone['number']" :error="$errors->first($phone['column'])" inputmode="tel" autocomplete="tel-national" maxlength="24" />
            </div>
        @endforeach
        <x-admiro::input id="email" name="email" type="email" :label="$label('email')" icon="mail" :value="$value('email')" :placeholder="text('placeholders.email')" :error="$errors->first('email')" :maxlength="$length['email']" />
        <x-admiro::input id="billing_email" name="billing_email" type="email" :label="$label('billing_email')" icon="mail" :hint="text('layout.organization_form.billing_email_hint')" :value="$value('billing_email')" :placeholder="text('placeholders.email')" :error="$errors->first('billing_email')" :maxlength="$length['billing_email']" />
    </fieldset>
</section>
