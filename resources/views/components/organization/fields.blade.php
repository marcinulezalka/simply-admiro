{{-- Pola danych organizacji (Organizations\OrganizationForm) w trzech kartach w kolejności: dane organizacji (nazwa, nazwa skrócona, NIP z maską, REGON, KRS, kolor — próbnik `type="color"` z Organization::color(), w podglądzie kropka SVG z atrybutem fill, bez stylów inline; slot na dodatkowe pola, np. organizację nadrzędną), dane adresowe z krajem (flaga kraju jako ikona w polu, x-admiro::flag-select; w nowym wpisie kraj domyślny aplikacji; Organizations\ContactCountries) i dane kontaktowe (telefon stacjonarny i komórkowy — każdy w osobnym wierszu: prefiks kraju z flagą z listy aktywnych krajów i numer, .ss-phone; e-mail, e-mail do rozliczeń). Karty w wyglądzie teczki (x-admiro::folder-card: nagłówek z ikoną na wypustce) z podglądem w formie prezentacyjnej (bez etykiet: nazwa, pod nią w jednym wierszu tylko wypełnione NIP, REGON, KRS — wartości krojem mono .ss-number; adres w dwóch wierszach z krajem; telefony z prefiksem i adresy e-mail z ikonami; bez danych — „Brak danych”); każde pole w osobnym wierszu; bez przypisów z formatem (format narzuca maska pola). Bez przycisku i znacznika <form>. Slot afterDetails: treść między kartą danych organizacji a kartą adresową (np. karta „Marka” z detached). organization: ?Organization (null = nowa); disabled: bool (tylko do odczytu: sam podgląd, a slot w wyłączonym fieldset); editable: bool (karty w dwóch stanach — podgląd z „Edytuj”, edycja z „Zapisz” i „Anuluj”, js/edit-card.js; karta z błędem walidacji otwiera się w edycji); customFields: bool (przy każdej karcie zakładka „Pola dodatkowe” — x-admiro::attributes-editor, sekcje CustomAttributes: dane organizacji — DEFAULT_SECTION, adres — `address`, kontakt — `contact`); Etykiety z text_data(Organization), wartości z old() albo z organizacji. --}}
@props(['organization' => null, 'disabled' => false, 'editable' => false, 'customFields' => false])
@php
    $model = \Simplysmart\Admiro\Models\Organization::class;
    $value = static fn (string $field): mixed => old($field, $organization?->getAttribute($field));
    $stored = static fn (string $field): ?string => filled($organization?->getAttribute($field)) ? (string) $organization->getAttribute($field) : null;
    $label = static fn (string $field): string => text_data($model, $field);
    $length = \Simplysmart\Admiro\Models\Organization::DETAIL_LENGTHS;
    $contactCountries = app(\Simplysmart\Admiro\Organizations\ContactCountries::class);
    $countries = $contactCountries->countries();
    $countryCode = $contactCountries->selectedCode($organization);
    $phones = array_map(static function (string $column) use ($contactCountries, $organization, $countryCode): array {
        $split = $contactCountries->split($organization?->getAttribute($column), $countryCode);
        $prefix = \Simplysmart\Admiro\Organizations\ContactCountries::prefixField($column);

        return ['column' => $column, 'prefix' => $prefix, 'country' => old($prefix, $split['country']), 'number' => old($column, $split['number']), 'shown' => filled($split['number']) ? trim($contactCountries->dialCode($split['country']) . ' ' . $split['number']) : null];
    }, \Simplysmart\Admiro\Models\Organization::PHONES);
    $dialCountries = $countries->filter(static fn ($country): bool => filled($country->dial_code));
    $flagsOf = static fn ($list): array => $list->mapWithKeys(static fn ($country): array => [$country->alpha_code_2 => $country->flag_icon])->all();
    $flags = $flagsOf($countries);
    $dialFlags = $flagsOf($dialCountries);
    $selectedCountry = old('country_code', $countryCode);
    $cardEditable = $editable && ! $disabled;
    $color = old('color', $organization?->color() ?? \Simplysmart\Admiro\Models\Organization::COLOR_PALETTE[0]);
    $withPreview = $editable || $disabled;
    $facts = array_filter(['nip' => $stored('nip') !== null ? \Simplysmart\Admiro\Enums\InputMask::TaxId->format($stored('nip')) : null, 'regon' => $stored('regon'), 'krs' => $stored('krs')]);
    $country = $countries->firstWhere('alpha_code_2', $organization?->getAttribute('country_code'));
    $cityLine = trim(implode(' ', array_filter([$stored('postal_code') !== null ? \Simplysmart\Admiro\Enums\InputMask::PostalCode->format($stored('postal_code')) : null, $stored('city')])));
    $sections = ['details' => \Simplysmart\Admiro\Attributes\CustomAttributes::DEFAULT_SECTION, 'address' => 'address', 'contact' => 'contact'];
    $extraTitle = $customFields ? text('layout.attributes.title') : null;
@endphp
<x-admiro::folder-card id="organization-details" icon="building" :title="text('layout.organization_form.details')" :extra-title="$extraTitle" :editable="$cardEditable" :editing="$errors->hasAny(['name', 'short_name', 'nip', 'regon', 'krs', 'color', \Simplysmart\Admiro\Attributes\CustomAttributes::FIELD . '.' . $sections['details'] . '.*'])">
    @if ($withPreview)
        <div class="ss-folder-card__preview ss-preview">
            <p class="ss-preview__title">{{ $stored('name') ?? '—' }}</p>
            @if ($stored('short_name') !== null && $stored('short_name') !== $stored('name'))
                <p class="ss-preview__line ss-muted">{{ $stored('short_name') }}</p>
            @endif
            @if ($facts !== [])
                <ul class="ss-preview__facts">
                    @foreach ($facts as $fact => $factValue)
                        <li><span class="ss-preview__fact-label">{{ $label($fact) }}</span> <span class="ss-number">{{ $factValue }}</span></li>
                    @endforeach
                </ul>
            @endif
            <p class="ss-preview__line"><svg class="ss-color-swatch" viewBox="0 0 12 12" aria-hidden="true" focusable="false"><circle cx="6" cy="6" r="6" fill="{{ $color }}" /></svg> <span class="ss-preview__fact-label">{{ $label('color') }}</span> {{ $color }}</p>
        </div>
    @endif
    @if ($disabled)
        @if (filled(trim((string) $slot)))
            <fieldset class="ss-form ss-fieldset" disabled>{{ $slot }}</fieldset>
        @endif
    @else
    <fieldset class="ss-form ss-fieldset ss-folder-card__form" data-ss-edit-fields>
        <x-admiro::input id="name" name="name" :label="$label('name')" icon="building" :value="$value('name')" :placeholder="text('placeholders.company')" :error="$errors->first('name')" required maxlength="255" />
        <x-admiro::input id="short_name" name="short_name" :label="$label('short_name')" :hint="text('layout.organization_form.short_name_hint')" :value="$value('short_name')" :error="$errors->first('short_name')" :maxlength="\Simplysmart\Admiro\Models\Organization::SHORT_NAME_MAX_LENGTH" />
        <x-admiro::input id="nip" name="nip" :label="$label('nip')" :mask="\Simplysmart\Admiro\Enums\InputMask::TaxId" :value="$value('nip')" :error="$errors->first('nip')" autocomplete="off" />
        <x-admiro::input id="regon" name="regon" :label="$label('regon')" :value="$value('regon')" :error="$errors->first('regon')" :maxlength="$length['regon']" inputmode="numeric" autocomplete="off" />
        <x-admiro::input id="krs" name="krs" :label="$label('krs')" :value="$value('krs')" :error="$errors->first('krs')" :maxlength="$length['krs']" inputmode="numeric" autocomplete="off" />
        <x-admiro::input id="color" name="color" type="color" :label="$label('color')" icon="palette" :value="$color" :error="$errors->first('color')" />
        {{ $slot }}
    </fieldset>
    @endif
    @if ($customFields)
        <x-slot:extra>
            @include('admiro::components.organization.custom-fields', ['section' => $sections['details']])
        </x-slot:extra>
    @endif
</x-admiro::folder-card>

{{ $afterDetails ?? '' }}

<x-admiro::folder-card id="organization-address" icon="map-pin" :title="text('layout.organization_form.address')" :extra-title="$extraTitle" :editable="$cardEditable" :editing="$errors->hasAny(['street', 'postal_code', 'city', 'country_code', \Simplysmart\Admiro\Attributes\CustomAttributes::FIELD . '.' . $sections['address'] . '.*'])">
    @if ($withPreview)
        <div class="ss-folder-card__preview ss-preview">
            @if ($stored('street') === null && $cityLine === '' && $country === null)
                <p class="ss-muted">{{ text('layout.organization_form.empty') }}</p>
            @else
                @if ($stored('street') !== null)<p class="ss-preview__line">{{ $stored('street') }}</p>@endif
                @if ($cityLine !== '')<p class="ss-preview__line">{{ $cityLine }}</p>@endif
                @if ($country !== null)<p class="ss-preview__icon-line"><x-admiro::flag :icon="$country->flag_icon" />{{ $country->translatedName() }}</p>@endif
            @endif
        </div>
    @endif
    @unless ($disabled)
    <fieldset class="ss-form ss-fieldset ss-folder-card__form" data-ss-edit-fields>
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
    @endunless
    @if ($customFields)
        <x-slot:extra>
            @include('admiro::components.organization.custom-fields', ['section' => $sections['address']])
        </x-slot:extra>
    @endif
</x-admiro::folder-card>

<x-admiro::folder-card id="organization-contact" icon="phone" :title="text('layout.organization_form.contact')" :extra-title="$extraTitle" :editable="$cardEditable" :editing="$errors->hasAny(['email', 'billing_email', ...array_column($phones, 'column'), ...array_column($phones, 'prefix'), \Simplysmart\Admiro\Attributes\CustomAttributes::FIELD . '.' . $sections['contact'] . '.*'])">
    @if ($withPreview)
        <div class="ss-folder-card__preview ss-preview">
            @php($shownPhones = array_filter($phones, static fn (array $phone): bool => $phone['shown'] !== null))
            @if ($shownPhones === [] && $stored('email') === null && $stored('billing_email') === null)
                <p class="ss-muted">{{ text('layout.organization_form.empty') }}</p>
            @else
                @foreach ($shownPhones as $phone)
                    <p class="ss-preview__icon-line"><x-admiro::icon :name="$phone['column'] === \Simplysmart\Admiro\Models\Organization::MOBILE_PHONE ? 'smartphone' : 'phone'" class="ss-preview__icon" /><span class="ss-sr-only">{{ $label($phone['column']) }}: </span>{{ $phone['shown'] }}</p>
                @endforeach
                @if ($stored('email') !== null)<p class="ss-preview__icon-line"><x-admiro::icon name="mail" class="ss-preview__icon" /><span class="ss-sr-only">{{ $label('email') }}: </span>{{ $stored('email') }}</p>@endif
                @if ($stored('billing_email') !== null && $stored('billing_email') !== $stored('email'))<p class="ss-preview__icon-line"><x-admiro::icon name="invoice" class="ss-preview__icon" /><span class="ss-sr-only">{{ $label('billing_email') }}: </span>{{ $stored('billing_email') }}</p>@endif
            @endif
        </div>
    @endif
    @unless ($disabled)
    <fieldset class="ss-form ss-fieldset ss-folder-card__form" data-ss-edit-fields>
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
    @endunless
    @if ($customFields)
        <x-slot:extra>
            @include('admiro::components.organization.custom-fields', ['section' => $sections['contact']])
        </x-slot:extra>
    @endif
</x-admiro::folder-card>
